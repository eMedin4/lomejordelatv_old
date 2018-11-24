<?php

namespace App\Library\Providers;

use App\Http\Controllers\Controller;
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

        $response = Request::get("https://unogs-unogs-v1.p.mashape.com/aaapi.cgi?q=-!1900,2018-!0,5-!0,10-!0-!Movie-!Any-!Any-!gt0-!&t=ns&cl=270&st=adv&ob=Relevance&p=1&sa=and",
            array(
                "X-Mashape-Key" => env('UNOGS_KEY'),
                "X-Mashape-Host" => env('UNOGS_HOST')
            )
        );

        if ($response->code != 200 || $response->body->COUNT == 0) {
            $this->output->message("Error o sin items en la respuestas de Netflix", true, $source);
            return;
        }

        $this->repository->resetNetflix();

        foreach ($response->body->ITEMS as $item) {

            $movie = $this->getMovieFromDb($item, $source);

            //si encontramos la pelicula en db
            if ($movie) {
                $this->repository->setNetflix($item->netflixid, $movie->id);
                $this->output->message("Guardada ok en db:Netflix-> $item->title con $movie->title", false, $source);
            } else {
                $this->output->message("No se puede guardar en db:Netflix-> $item->title , $item->netflixid , $item->imdbid, $item->released . No se encuentra en db", true, $source);
            }
        }
    }

    // Devuelve modelo movie o false
    public function getMovieFromDb($item, $source)
    {
        //Buscamos en verificadas
        $verify = $this->repository->checkVerify($item->netflixid, 'nf');
        if ($verify) {
            $movie = $this->repository->getMovieFromId($verify->id_1, 'fa');
            //si está en verificadas pero no existe en db la creamos
            if (is_null($movie)) {
                $this->mixmovies->setFromFaId($source, 'film' . $verify->id_1);
                $movie = $this->repository->getMovieFromId($verify->id_1, 'fa');
            }
            return $movie;
        }
        
        //Buscamos por el imid
        if (!empty($item->imdbid)) {
            $movie = $this->repository->getMovieFromId($item->imdbid, 'im');
            if ($movie) {
                $checkYears = $this->format->checkYears($movie->year, $item->released,2);
                if ($checkYears['response'] == false) {
                    $this->output->message("Netflix $item->imdbid ( $item->released ) : No coincide con el año de db $movie->year", true, $source);
                }
                return $movie;
            }
        }

        //Buscamos por título y año
        $movie = $this->repository->getMovieFromNetflix($this->format->decode($item->title), $item->released);
        if ($movie) {
            return $movie;
        }

        return false;
    }






}