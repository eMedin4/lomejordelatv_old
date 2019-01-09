<?php

namespace App\Library\Providers;

use Unirest\Request;
use Carbon\Carbon;
use App\Library\Output;
use App\Library\Format;
use App\Library\NetflixRepository;
use App\Library\GenericRepository;
use App\Library\ItemCreation;

class Netflix
{

    private $output;
    private $netflixRepository;
    private $genericRepository;
    private $repository;
    private $format;
    private $itemCreation;


    public function __Construct(Output $output, NetflixRepository $netflixRepository, GenericRepository $genericRepository, Format $format, ItemCreation $itemCreation)
	{
        $this->netflixRepository = $netflixRepository;
        $this->genericRepository = $genericRepository;
        $this->format = $format;
        $this->output = $output;
        $this->itemCreation = $itemCreation;
	}


    public function runAll()
    {
        $this->netflixRepository->reset();

        $page = 1;
        do {
            $response = $this->request('items', $page);
            //dd($response);
            if ($response) {
                foreach ($response->body->ITEMS as $item) {

                    //buscamos la película en db
                    $movie = $this->searchItem($item->netflixid, $item->imdbid, $item->title, $item->released, $item->type);

                    if ($movie) {

                        //guardamos la pelicula
                        $this->netflixRepository->setNetflix($item->netflixid, $movie->id, $item->type, $movie->fa_popularity);
                        $this->output->message("Guardada ok en db:Netflix-> $item->netflixid : $item->title -> $movie->fa_id  : $movie->title", false);
                    }
                }
            }
            $page++;

        } while (!empty($response->body->ITEMS));

        $this->netflixRepository->resetDates();
        $this->getDates('new');
        $this->getDates('expire');
        $this->getSeasons();
    }

    public function runByPages($pages)
    {
        foreach ($pages as $page) {

            $response = $this->request('items', $page);
            if ($response) {
                foreach ($response->body->ITEMS as $item) {

                    //buscamos la película en db
                    $movie = $this->searchItem($item->netflixid, $item->imdbid, $item->title, $item->released, $item->type);

                    if ($movie) {

                        //guardamos la pelicula
                        $this->netflixRepository->setNetflix($item->netflixid, $movie->id, $item->type, $movie->fa_popularity);
                        $this->output->message("Creada ok en db:Netflix-> $item->netflixid : $item->title -> $movie->fa_id  : $movie->title", false);
                    }
                }
            }

        }
    }

    public function runId($nfid)
    {
        $response = $this->request('single', null, $nfid);

        $netflixid = $response->body->RESULT->nfinfo->netflixid;
        $imdbid = $response->body->RESULT->imdbinfo->imdbid;
        $title = $response->body->RESULT->nfinfo->title;
        $released = $response->body->RESULT->nfinfo->released;
        $type = $response->body->RESULT->nfinfo->type;

        $movie = $this->searchItem($netflixid, $imdbid, $title, $released, $type);

        if ($movie) {

            //guardamos la pelicula
            $this->netflixRepository->setNetflix($netflixid, $movie->id, $type, $movie->fa_popularity);
            $this->output->message("Guardada ok en db:Netflix-> $netflixid : $title -> $movie->fa_id : $movie->title", false);
        } 
    }


    public function getDates($column)
    {

        $response = $this->request($column);

        if ($response) {
            $i = 0;
            foreach($response->body->ITEMS as $item) {

                $update = $this->netflixRepository->setDates($item->netflixid, $column, $item->unogsdate);
                if (!$update) {
                    $ban = $this->genericRepository->checkBan($item->netflixid, 'nf');
                    if (!$ban) $this->output->message("$item->netflixid : $item->title -> No se actualiza la columna $column de Netflix", true, 'error');
                } else {
                    $i++;
                }

            }
            $this->output->message("Actualimos la columna $column con $i entradas", false, 'comment');
        }
    }


    public function request($query, $page = 1, $nfid = null)
    {
        if ($query == 'items') $url = "https://unogs-unogs-v1.p.mashape.com/aaapi.cgi?q=-!1900,2018-!0,5-!0,10-!0-!Any-!Any-!Any-!gt0-!&t=ns&cl=270&st=adv&ob=Relevance&p=$page&sa=and";
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



    /*
        getMovieFromDb
        Funcion: Buscamos item de Netflix en tablas Netflix, baneadas, verificadas, y movies
        Retorna: false si la encuentra en Netflix, bans o verifys; $movie si la encuentra nueva en movies, si no la encuentra retorna false y escribe en el log
    */
    public function searchItem($netflixid, $imdbid, $title, $released, $type)
    {
        // Comprobamos y terminamos si existe
        $exist = $this->netflixRepository->existAndUpdate($netflixid);
        if ($exist) {
            $this->output->message("Ya existe, la actualizamos $title : $netflixid", false);
            return false;
        }

        // Buscando en baneadas
        $ban = $this->genericRepository->checkBan($netflixid, 'nf');
        if ($ban) {
            $this->output->message("Baneada $title : $netflixid", false);
            return false;
        } 

        // Buscamos en verificadas
        $verify = $this->genericRepository->checkVerify($netflixid, 'nf');
        
        // Si existe en verificadas -> Buscamos el modelo movie
        if ($verify) {
            $movie = $this->genericRepository->getMovieFromId($verify, 'fa');
            
            // Si no existe el modelo lo creamos
            if (!$movie) {
                $itemCreation = $this->itemCreation->runId('film' . $verify);

                // Si da error al crear el Item
                if (!$itemCreation) {
                    $this->output->message("Error al crear en ItemCreation fa: $verify", true, 'error');
                    return false;
                }
            }

            // Devolvemos el modelo procedente de verificadas
            return $movie;
        }
        
        // Buscamos por imdb_id
        if (!empty($imdbid)) {
            $movie = $this->genericRepository->getMovieFromId($imdbid, 'im');
            if ($movie) {
                $checkYears = $this->format->checkYears($movie->year, $released,2);
                if ($checkYears['response'] == false) {
                    $this->output->message("Netflix $netflixid : ($released) : Aceptada con reparos, Encontramos coincidencuia en imid pero no en año $movie->fa_id : ($movie->year)", true, 'error');
                }
                return $movie;
            }
        }

        // Buscamos por título y año
        $movie = $this->netflixRepository->searchItem($this->format->decode($title), $released, $type);
        if ($movie) {
            return $movie;
        }

        $this->output->message("No se puede guardar en db:Netflix-> $title , $netflixid , $imdbid, $released, $type : No se encuentra en db", true, 'error');
        return false;
    }

    public function getSeasons()
    {
        $now = Carbon::now();
        $dateLimit = Carbon::now()->subMonths(3);

        //Recibimos coleccion de items de Netflix
        $items = $this->netflixRepository->getItemsForSeasons($dateLimit);

        foreach ($items as $item) {

            //comprobamos si este item tiene algo en la tabla seasons
            if($item->movie->seasonsTable()->exists()) { //Si existe true si no false
                $last = $item->movie->seasonsTable->max('number'); // numero con el último episodio
            } else {

                //si no tiene nada en la tabla seasons vamos a intentar descargarlo de tmdb
                $last = $this->itemCreation->runSeasons($item->movie->id, $item->movie->tm_id);
            }   

            //Si no encontramos seasons provisionalmente no descargamos de netflix, para revisar
            if ($last) {
                $response = $this->request('single', 1, $item->netflix_id);

                if ($response) {
                    $countries = $response->body->RESULT->country;
     
                    foreach ($countries as $country) {
                        if ($country->country == 'Spain ') {
                            $seasons = $country->seasondet; //array [0 => 1, 1 => 2, ...]
                            $this->genericRepository->setProvidersSeasons('nf', $item->id, $seasons, $last);
                            $this->output->message("Netflix $item->netflix_id actualizamos providers_seasons", false);
    
                        }
                    }
                }
            }
            
            $this->netflixRepository->updateGetSeasonsAt($item->netflix_id, $now);

            
        }
    }

    public function test($page) {
        $response = $this->request('items', $page);
            if ($response) {

                $collection = collect($response->body->ITEMS);
                dd($collection);

                foreach ($collection as $item) {

                    //buscamos la película en db
                    $movie = $this->netflixRepository->getItem($item->netflixid);
                    if ($movie) {
                        $item->put('state', 'database');
                        continue;
                    }
                    
                    // Buscando en baneadas
                    $ban = $this->genericRepository->checkBan($item->netflixid, 'nf');
                    if ($ban) {
                        $item->put('state', 'ban');
                        continue;
                    }

                    // Buscamos en verificadas
                    $verify = $this->genericRepository->checkVerify($item->netflixid, 'nf');
                    if ($verify) {
                        
                        // Si existe en verificadas -> Buscamos el modelo movie
                        $movie = $this->genericRepository->getMovieFromId($verify, 'fa');
                        
                        // Si no existe el modelo lo creamos
                        if (!$movie) {
                            $itemCreation = $this->itemCreation->runId('film' . $verify);
                    
                            // Si da error al crear el Item
                            if (!$itemCreation) {
                                $item->put('state', 'verificadas -> no database -> error al crearla');
                                continue;
                            }

                            $item->put('state', 'verificadas -> no database -> se crea ok');
                            continue;
                        }

                    }



                }
            }
    }
}