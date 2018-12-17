<?php
namespace App\Library;

use Goutte\Client;
use App\Library\Repository;
use App\Library\Output;
use App\Library\Providers\FilmAffinity;
use App\Library\Providers\Themoviedb;

class CreateShows
{

    private $repository;
	private $output;
	private $filmaffinity;
	private $themoviedb;

    public function __Construct(Repository $repository, Output $output, Filmaffinity $filmaffinity, Themoviedb $themoviedb)
	{
		$this->repository = $repository;
		$this->output = $output;
		$this->filmaffinity = $filmaffinity;
		$this->themoviedb = $themoviedb;

		ini_set('memory_limit', '-1');
        set_time_limit(28800);
        $this->themoviedb->updateAllShowGenres();
	}


    public function processAll($explicitLetter, $explicitPage, $toTheEnd, $fullUpdate)
    {

        $explicitLetter = strtoupper($explicitLetter);
        $letters = ['0-9','A','B','C','D','E','F','G','H','I','J','K','L','M','N','0','P','Q','R','S','T','U','V','W','X','Y','Z'];

        //Si recibimos letra, pagina y totheend
        if ($explicitLetter && $explicitPage && $toTheEnd) {
            $position = array_search($explicitLetter, $letters);
            $letters = array_slice($letters, $position, NULL, TRUE);
            $i = 1;
            foreach ($letters as $letter) {
                if ($i == 1) $this->processLetter($letter, $explicitPage, $fullUpdate);
                else $this->processLetter($letter, null, $fullUpdate);
                $i++;
            }

        //si recibimos letra y pagina
        } elseif ($explicitLetter && $explicitPage) {
            $this->processExplicitPage($explicitLetter, $explicitPage, $fullUpdate);

        //si solo recibimos letra
        } elseif ($explicitLetter) {
            $this->processLetter($explicitLetter, $fullUpdate);

        //si no recibimos ninguna opcion
        } else {
            foreach ($letters as $letter) {
                $this->processLetter($letter, null, $fullUpdate);
            }
        }
    }


    public function processLetter($letter, $explicitPage, $fullUpdate)
    {
        //Lanzamos scraper de pagina de filmaffinity
        $startPage = $explicitPage ? $explicitPage : 1;
        $url = "https://www.filmaffinity.com/es/allfilms_" . $letter . "_" . $startPage . ".html";
        $client = new Client();
        $crawler = $client->request('GET', $url);
        if ($client->getResponse()->getStatus() !== 200) {
            $this->output->message("Error: la url generada no es válida: $url", true);
            return;
        } 
        $this->output->message("Entramos en $url", false);

        do {
            //Recorremos las cards de la pagina
            $crawler->filter('.movie-card')->each(function($element) use($client, $fullUpdate) {
                $this->processCard($element, $client, 10, $fullUpdate);
            });

            //Si hay página siguiente dentro de la misma letra avanzamos, si no saldremos a la siguiente letra
            if ($crawler->filter('.pager .current')->nextAll()->count()) {
                $upPage = $crawler->filter('.pager .current')->nextAll()->link();
                $crawler = $client->click($upPage);  
            } else {
                $upPage = false;
            }	
        } while ($upPage);
    }


    public function processExplicitPage($letter, $page, $fullUpdate)
    {

        //Lanzamos scraper de pagina de filmaffinity
        $url = "https://www.filmaffinity.com/es/allfilms_" . $letter . "_" . $page . ".html";
        $client = new Client();
        $crawler = $client->request('GET', $url);
        if ($client->getResponse()->getStatus() !== 200) {
            $this->output->message("Error: la url generada no es válida: $url", true);
            return;
        } 
        $this->output->message("Entramos en $url", false);

        //Recorremos las cards de la pagina
        $crawler->filter('.movie-card')->each(function($element) use($client, $fullUpdate) {
            $this->processCard($element, $client, 10, $fullUpdate);
        });
    }


    public function processCard($element, $client, $minVotes = 0, $fullUpdate)
    {
        //Scrapeamos movie card
        $card = $this->filmaffinity->getCard($element);

        //Filtramos solo por series
        if (!preg_match('/\((Serie de TV)\)|\((Miniserie de TV)\)/', $card['fa_title'])) return;

        //Filtramos por numero minimo de votos
        if ($card['fa_count'] < $minVotes) return;
        
        //Solo actualizamos si ya existe (a no ser que se especifique la opcion fullUpdate)
        if (!$fullUpdate) {
            if ($this->repository->checkIfMovieExist($card['fa_id'])) {
                $this->repository->update($card);
                $this->output->message( $card['fa_title'] . " : Ya existe, la actualizamos", false);
                return;
            }
        }
        
        //click y entramos
		$crawler = $client->click($card['href']->link());
		$this->output->message($card['fa_title'] . " : Entramos en la pagina de filmaffinity", false);
				
		//Scrapeamos
        $faData = $this->filmaffinity->getMovie($crawler);
        
        if ($faData['response'] == false) {
            $this->output->message($faData['message'], $faData['revision']);
            return;
        }

        //datos de tmdb
        $tmData = $this->themoviedb->getMovie($faData, 'artisan', 'show');
        
        if ($tmData['response'] == false) {
            $this->output->message($tmData['message'], true);
            return;
        } 
        
        $this->output->message($faData['fa_title'] . " : Datos de Themoviedb ok", false);
        $fullData = array_merge($faData, $tmData);
        $this->repository->storeItem($fullData, 'artisan', 'show');
        $this->output->message($fullData['fa_title'] . " : Guardada ok en base de datos", false); 
    }
    
}


