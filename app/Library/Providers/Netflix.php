<?php

namespace App\Library\Providers;

use Unirest\Request;
use App\Library\Output;
use App\Library\Format;
use App\Library\Repository;
use App\Library\MixMovies;

class Netflix
{
    private $output;
    private $repository;
    private $format;
    private $mixmovies;

    public function __Construct(Output $output, Repository $repository, Format $format, MixMovies $mixmovies)
	{
        $this->repository = $repository;
        $this->format = $format;
        $this->output = $output;
        $this->mixmovies = $mixmovies;
	}

    public function getMovies($source)
    {
        $this->repository->resetNetflix();
        $i = 1;

        do {

            $response = Request::get("https://unogs-unogs-v1.p.mashape.com/aaapi.cgi?q=-!1900,2018-!0,5-!0,10-!0-!Movie-!Any-!Any-!gt0-!&t=ns&cl=270&st=adv&ob=Relevance&p=$i&sa=and",
                array(
                    "X-Mashape-Key" => env('UNOGS_KEY'),
                    "X-Mashape-Host" => env('UNOGS_HOST')
                )
            );

            if ($response->code != 200 || $response->body->COUNT == 0) {
                $this->output->message("Error o sin items en la respuestas de Netflix", true, $source);
                return;
            }

            $this->output->message("·············Procesamos página de Netflix $i", false, $source);
            foreach ($response->body->ITEMS as $item) {

                $movie = $this->getMovieFromDb($item->netflixid, $item->imdbid, $item->title, $item->released, $source);
                //si encontramos la pelicula en db
                if ($movie['response']) {
                    $this->repository->setNetflix($item->netflixid, $movie['movie']->id);
                    $this->output->message("Guardada ok en db:Netflix-> $item->netflixid : $item->title -> " . $movie['movie']->fa_id . " : " . $movie['movie']->title, false, $source);
                } elseif ( ($movie['response'] == false) && ($movie['reason'] == 'ban') ) {
                    $this->output->message("Baneada $item->title : $item->netflixid", false, $source);
                } elseif ( ($movie['response'] == false) && ($movie['reason'] == 'importError') ) {
                    $this->output->message("Error $item->title : $item->netflixid : film" . $movie['faid'], true, $source);
                } else {
                    $this->output->message("No se puede guardar en db:Netflix-> $item->title , $item->netflixid , $item->imdbid, $item->released . No se encuentra en db", true, $source);
                }
            }

            $i++;

        } while (!empty($response->body->ITEMS));

        $this->getNew($source);
        $this->getExpiring($source);

    }

    public function getMovie($source, $nfid)
    {
        $response = Request::get("https://unogs-unogs-v1.p.mashape.com/aaapi.cgi?t=loadvideo&q=$nfid",
            array(
                "X-Mashape-Key" => env('UNOGS_KEY'),
                "X-Mashape-Host" => env('UNOGS_HOST')
            )
        );

        if ($response->code != 200 || empty($response->body)) {
            $this->output->message("Error o sin items en la respuestas de Netflix", true, $source);
            return;
        }

        $netflixid = $response->body->RESULT->nfinfo->netflixid;
        $imdbid = $response->body->RESULT->imdbinfo->imdbid;
        $title = $response->body->RESULT->nfinfo->title;
        $released = $response->body->RESULT->nfinfo->released;
        $movie = $this->getMovieFromDb($netflixid, $imdbid, $title, $released, $source);

        //si encontramos la pelicula en db
        if ($movie['response']) {
            $this->repository->setNetflix($netflixid, $movie['movie']->id);
            $this->output->message("Guardada ok en db:Netflix-> $netflixid : $title -> " . $movie['movie']->fa_id . " : " . $movie['movie']->title, false, $source);
        } elseif ( ($movie['response'] == false) && ($movie['reason'] == 'ban') ) {
            $this->output->message("Baneada $title : $netflixid", false, $source);
        } else {
            $this->output->message("No se puede guardar en db:Netflix-> $title , $netflixid , $imdbid, $released . No se encuentra en db", true, $source);
        }

    }

    public function getNew($source)
    {

        $response = Request::get("https://unogs-unogs-v1.p.mashape.com/aaapi.cgi?q=get:new30:ES&p=1&t=ns&st=adv",
            array(
                "X-Mashape-Key" => env('UNOGS_KEY'),
                "X-Mashape-Host" => env('UNOGS_HOST')
            )
        );

        if ($response->code != 200 || empty($response->body)) {
            $this->output->message("Error o sin items en la respuestas de Netflix", true, $source);
            return;
        }

        $this->output->message("·············Procesamos Netflix New", false, $source);
        $this->repository->resetNetflixDates();

        $i = 0;
        foreach($response->body->ITEMS as $item) {
            if ($item->type == "movie") {
                $update = $this->repository->setNetflixDates($item->netflixid, 'new', $item->unogsdate);
                if (!$update) {
                    $ban = $this->repository->checkBan($item->netflixid, 'nf');
                    if (!$ban) $this->output->message("$item->netflixid : $item->title -> No se actualiza la columna NEW de Netflix", true, $source);
                } else {
                    $i++;
                }
            }
        }

        $this->output->message("Actualimos Netflix > New $i entradas", false, $source);

    }

    public function getExpiring($source)
    {

        $response = Request::get("https://unogs-unogs-v1.p.mashape.com/aaapi.cgi?q=get:exp:ES&p=1&t=ns&st=adv",
            array(
                "X-Mashape-Key" => env('UNOGS_KEY'),
                "X-Mashape-Host" => env('UNOGS_HOST')
            )
        );

        if ($response->code != 200 || empty($response->body)) {
            $this->output->message("Error o sin items en la respuestas de Netflix", true, $source);
            return;
        }

        $this->output->message("·············Procesamos Netflix Expiring", false, $source);

        $i = 0;
        foreach($response->body->ITEMS as $item) {
            if ($item->type == "movie") {
                $update = $this->repository->setNetflixDates($item->netflixid, 'expire', $item->unogsdate);
                if (!$update) {
                    $ban = $this->repository->checkBan($item->netflixid, 'nf');
                    if (!$ban) $this->output->message("$item->netflixid : $item->title -> No se actualiza la columna EXPIRE de Netflix", true, $source);
                } else {
                    $i++;
                }
            }
        }

        $this->output->message("Actualimos Netflix > Expire $i entradas", false, $source);

    }

    /*
        getMovieFromDb
        Funcion: Busca del api de netflix en nuestra base de datos
        Retorna: response = true, movie=modelo o response=false, reason=?
    */
    public function getMovieFromDb($netflixid, $imdbid, $title, $released, $source)
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
                $setFromFaId = $this->mixmovies->setFromFaId($source, 'film' . $verify);
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
                    $this->output->message("Netflix $netflixid : ($released) : No coincide con el año de db $movie->fa_id : ($movie->year)", true, $source);
                }
                return ['response' => true, 'movie' => $movie];
            }
        }

        //Buscamos por título y año
        $movie = $this->repository->getMovieFromNetflix($this->format->decode($title), $released);
        if ($movie) {
            return ['response' => true, 'movie' => $movie];
        }

        return ['response' => false, 'reason' => 'miss'];
    }






}