<?php

namespace App\Library\Providers;

use Unirest\Request;
use App\Library\Output;
use App\Library\Format;
use App\Library\Repository;
use App\Library\ItemCreation;

class Netflix
{

    private $output;
    private $repository;
    private $format;
    private $itemCreation;


    public function __Construct(Output $output, Repository $repository, Format $format, ItemCreation $itemCreation)
	{
        $this->repository = $repository;
        $this->format = $format;
        $this->output = $output;
        $this->itemCreation = $itemCreation;
	}


    public function runAll()
    {
        //$this->repository->resetNetflix();

        $page = 1;
        do {
            $response = $this->processPage('full', $page);
            dd($response);
            if ($response) {
                foreach ($response->body->ITEMS as $item) {
                    //buscamos la película en db
                    $movie = $this->getMovieFromDb($item->netflixid, $item->imdbid, $item->title, $item->released, $item->type);
                    //guardamos la pelicula
                    $this->processMovie($item, $movie);
                }
            }
            $page++;

        } while (!empty($response->body->ITEMS));

        $this->repository->resetNetflixDates();
        $this->getDates('new');
        $this->getDates('expire');
    }

    public function runByPages($pages)
    {
        $this->repository->resetNetflix();

        foreach ($pages as $page) {

            $response = $this->processPage('full', $page);
            if ($response) {
                foreach ($response->body->ITEMS as $item) {
                    //buscamos la película en db
                    $movie = $this->getMovieFromDb($item->netflixid, $item->imdbid, $item->title, $item->released, $item->type);
                    //guardamos la pelicula
                    $this->processMovie($item, $movie);
                }
            }

        }

        $this->repository->resetNetflixDates();
        $this->getDates('new');
        $this->getDates('expire');
    }

    public function runId($nfid)
    {
        $response = $this->processPage('single', null, $nfid);

        $netflixid = $response->body->RESULT->nfinfo->netflixid;
        $imdbid = $response->body->RESULT->imdbinfo->imdbid;
        $title = $response->body->RESULT->nfinfo->title;
        $released = $response->body->RESULT->nfinfo->released;
        $type = $response->body->RESULT->nfinfo->type;
        $movie = $this->getMovieFromDb($netflixid, $imdbid, $title, $released, $type);

        //si encontramos la pelicula en db
        if ($movie['response']) {
            $this->repository->setNetflix($netflixid, $movie['movie']->id, $type, $movie['movie']->fa_popularity);
            $this->output->message("Guardada ok en db:Netflix-> $netflixid : $title -> " . $movie['movie']->fa_id . " : " . $movie['movie']->title, false);
        } elseif ( ($movie['response'] == false) && ($movie['reason'] == 'ban') ) {
            $this->output->message("Baneada $title : $netflixid", false, 'error');
        } else {
            $this->output->message("No se puede guardar en db:Netflix-> $title , $netflixid , $imdbid, $released . No se encuentra en db", true, 'error');
        }

    }


    public function getDates($column)
    {

        $response = $this->processPage($column);

        if ($response) {
            $i = 0;
            foreach($response->body->ITEMS as $item) {

                $update = $this->repository->setNetflixDates($item->netflixid, $column, $item->unogsdate);
                if (!$update) {
                    $ban = $this->repository->checkBan($item->netflixid, 'nf');
                    if (!$ban) $this->output->message("$item->netflixid : $item->title -> No se actualiza la columna $column de Netflix", true, 'error');
                } else {
                    $i++;
                }

            }
            $this->output->message("Actualimos la columna $column con $i entradas", false, 'comment');
        }
    }


    public function processPage($query, $page = 1, $nfid = null)
    {
        if ($query == 'full') $url = "https://unogs-unogs-v1.p.mashape.com/aaapi.cgi?q=-!1900,2018-!0,5-!0,10-!0-!Any-!Any-!Any-!gt0-!&t=ns&cl=270&st=adv&ob=Relevance&p=$page&sa=and";
        elseif ($query == 'new') $url = "https://unogs-unogs-v1.p.mashape.com/aaapi.cgi?q=get:new30:ES&p=$page&t=ns&st=adv";
        elseif ($query == 'expire') $url = "https://unogs-unogs-v1.p.mashape.com/aaapi.cgi?q=get:exp:ES&p=$page&t=ns&st=adv";
        elseif ($query == 'single') $url = "https://unogs-unogs-v1.p.mashape.com/aaapi.cgi?t=loadvideo&q=$nfid";
        $response = Request::get($url,
            array(
                "X-Mashape-Key" => env('UNOGS_KEY'),
                "X-Mashape-Host" => env('UNOGS_HOST')
            )
        );
        if ($response->code != 200) {
            $this->output->message("Error en la respuestas de Netflix", true, 'error');
            return false;
        }
        $this->output->message("Procesamos página de Netflix $query pagina: $page", false, 'comment');
        return $response;
    }


    public function processMovie($item, $movie)
    {
        if ($movie['response']) {
            $this->repository->setNetflix($item->netflixid, $movie['movie']->id, $item->type, $movie['movie']->fa_popularity);
            $this->output->message("Guardada ok en db:Netflix-> $item->netflixid : $item->title -> " . $movie['movie']->fa_id . " : " . $movie['movie']->title, false);
        } elseif ( ($movie['response'] == false) && ($movie['reason'] == 'ban') ) {
            $this->output->message("Baneada $item->title : $item->netflixid", false, 'error');
        } elseif ( ($movie['response'] == false) && ($movie['reason'] == 'importError') ) {
            $this->output->message("Error al crearla en bd $item->title : $item->netflixid : film" . $movie['faid'], true, 'error');
        } else {
            $this->output->message("No se puede guardar en db:Netflix-> $item->title , $item->netflixid , $item->imdbid, $item->released, $item->type . No se encuentra en db", true, 'error');
        }
    }


    /*
        getMovieFromDb
        Funcion: Busca del api de netflix en nuestra base de datos
        Retorna: response = true, movie=modelo o response=false, reason=?
    */
    public function getMovieFromDb($netflixid, $imdbid, $title, $released, $type)
    {
        //Buscamos en baneadas
        $ban = $this->repository->checkBan($netflixid, 'nf');
        if ($ban) return ['response' => false, 'reason' => 'ban'];

        //Buscamos en verificadas
        $verify = $this->repository->checkVerify($netflixid, 'nf');
        if ($verify) {
            $movie = $this->repository->getMovieFromId($verify, 'fa');
            //si está en verificadas pero no existe en db la creamos
            if (is_null($movie)) {
                $setFromFaId = $this->itemCreation->runId('film' . $verify);
                if ($setFromFaId == false) return ['response' => false, 'reason' => 'importError', 'faid' => $verify];
                $movie = $this->repository->getMovieFromId($verify, 'fa');
            }
            return ['response' => true, 'movie' => $movie];
        }
        
        //Buscamos por el imid
        if (!empty($imdbid)) {
            $movie = $this->repository->getMovieFromId($imdbid, 'im');
            if ($movie) {
                $checkYears = $this->format->checkYears($movie->year, $released,2);
                if ($checkYears['response'] == false) {
                    $this->output->message("Netflix $netflixid : ($released) : Aceptada con reparos, Encontramos coincidencuia en imid pero no en año $movie->fa_id : ($movie->year)", true, 'error');
                }
                return ['response' => true, 'movie' => $movie];
            }
        }

        //Buscamos por título y año
        $movie = $this->repository->getMovieFromNetflix($this->format->decode($title), $released, $type);
        if ($movie) {
            return ['response' => true, 'movie' => $movie];
        }

        return ['response' => false, 'reason' => 'miss'];
    }






}