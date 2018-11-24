<?php
namespace App\Library;

use Goutte\Client;
use App\Library\Repository;
use App\Library\Output;
use App\Library\Format;
use App\Library\Providers\FilmAffinity;
use App\Library\Providers\Themoviedb;
Use App\Library\Images;
Use App\Library\MixMovies;

class Testing
{

    private $repository;
	private $output;
	private $filmaffinity;
    private $themoviedb;
    private $format;
    private $images;
    private $mixmovies;

    public function __Construct(Repository $repository, Output $output, Filmaffinity $filmaffinity, Themoviedb $themoviedb, Format $format, Images $images, MixMovies $mixmovies)
	{
		$this->repository = $repository;
		$this->output = $output;
		$this->filmaffinity = $filmaffinity;
        $this->themoviedb = $themoviedb;
        $this->format = $format;
        $this->images = $images;
        $this->mixmovies = $mixmovies;
	}
    
    public function faTmTest($faid, $source, $withDetails, $more)
    {
        //Datos de mi db
        $dbData = $this->repository->getMovieFromFaId($this->format->integer($faid));
        //scraper fa
        $url = 'https://www.filmaffinity.com/es/' . $faid . '.html';
        $client = new Client();
        $crawler = $client->request('GET', $url);
        if ($client->getResponse()->getStatus() !== 200) {
            return ['response' => false, 'message' => 'No se encuentra la url de filmaffinity'];
        }
        $faData = $this->filmaffinity->getMovie($crawler);

        if ($faData['response'] == true) {
            //como no deja enlazar la imagen de fa la descargamos en un directorio temporal
            $this->images->saveFaToTemp($faData['fa_image'], $faData['fa_id']);
        }
        
        //buscamos en tm
        $tmData = $this->themoviedb->getAllResults($faData, $withDetails, $more);

        $data = ['response' => true, 'db' => $dbData, 'fa' => $faData, 'tm' => $tmData];

        return $data;
    }

    public function setFaTest($faid, $source2, $id2)
    {
        //retorna un message
        $faidint = $this->format->integer($faid);
        $setVerify = $this->repository->setVerify('fa', $faidint, $source2, $id2); //true o false
        $setFromFaId = $this->mixmovies->setFromFaId('browse', $faid); //false o [status(updated o created), id]
        if ($setVerify == true) $setVerifyMessage = 'Se inserta correctamente en Verified'; 
        else $setVerifyMessage = "Error. Ya existía en Verified";
        if ($setFromFaId == false) return "$faidint : $setVerifyMessage . No se actualiza en Movie porque devuelve error";
        if ($setFromFaId['status'] == 'updated') return "$faidint : $setVerifyMessage . Ya existía en Movie, se actualiza ok";
        if ($setFromFaId['status'] == 'created') return "$faidint : $setVerifyMessage . Ya existía en Movie, se actualiza ok";
        return "Hay algún error";
    }
}