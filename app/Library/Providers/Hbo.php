<?php

namespace App\Library\Providers;






use Goutte\Client;

class Hbo
{


    public function __Construct()
	{

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