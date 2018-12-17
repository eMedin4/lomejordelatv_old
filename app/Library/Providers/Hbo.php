<?php

namespace App\Library\Providers;

use App\Http\Controllers\Controller;
use Unirest\Request;
use App\Library\Output;
use App\Library\Format;
use App\Library\Repository;
use App\Library\MixMovies;




use Goutte\Client;

class Hbo
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

        

    }

    public function getMovie($source, $nfid)
    {

    }

    /*
        getMovieFromDb
        Funcion: Busca del api de netflix en nuestra base de datos
        Retorna: response = true, movie=modelo o response=false, reason=?
    */
    public function getMovieFromDb($netflixid, $imdbid, $title, $released, $source)
    {

    }






}