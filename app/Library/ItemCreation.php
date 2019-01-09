<?php
namespace App\Library;

use Goutte\Client;
use App\Library\GenericRepository;
use App\Library\ItemRepository;
use App\Library\Output;
use App\Library\Providers\FilmAffinity;
use App\Library\Providers\Themoviedb;
use App\Library\Algorithm;

class ItemCreation
{

    private $genericRepository;
    private $itemRepository;
	private $output;
	private $filmaffinity;
    private $themoviedb;
    private $algorithm;

    public function __Construct(GenericRepository $genericRepository, ItemRepository $itemRepository, Output $output, Filmaffinity $filmaffinity, Themoviedb $themoviedb, Algorithm $algorithm)
	{
        $this->genericRepository = $genericRepository;
		$this->itemRepository = $itemRepository;
		$this->output = $output;
		$this->filmaffinity = $filmaffinity;
        $this->themoviedb = $themoviedb;
        $this->algorithm = $algorithm;

        //Actualizamos Tmdb genres
        $this->itemRepository->updateTmdbGenres($this->themoviedb->updateTmdbGenres());
	}


    public function run($explicitLetter, $explicitPage, $toTheEnd, $fullUpdate)
    {

        $explicitLetter = strtoupper($explicitLetter);
        $letters = ['0-9','A','B','C','D','E','F','G','H','I','J','K','L','M','N','0','P','Q','R','S','T','U','V','W','X','Y','Z'];

        //Si recibimos letra, pagina y totheend scrapeamos todo desde esa pagina
        if ($explicitLetter && $explicitPage && $toTheEnd) {
            $position = array_search($explicitLetter, $letters);
            $letters = array_slice($letters, $position, NULL, TRUE);
            $i = 1;
            foreach ($letters as $letter) {
                if ($i == 1) $this->processLetter($letter, $explicitPage, $fullUpdate);
                else $this->processLetter($letter, null, $fullUpdate);
                $i++;
            }

        //si recibimos letra y pagina scrapeamos solo esa letra y pagina
        } elseif ($explicitLetter && $explicitPage) {
            $this->processExplicitPage($explicitLetter, $explicitPage, $fullUpdate);

        //si solo recibimos letra scrapeamos toda la letra
        } elseif ($explicitLetter) {
            $this->processLetter($explicitLetter, null, $fullUpdate);

        //si no recibimos ninguna opcion scrapeamos todo
        } else {
            foreach ($letters as $letter) {
                $this->processLetter($letter, null, $fullUpdate);
            }
        }
    }

    public function runId($id)
    {
        $client = new Client();
        $crawler = $this->requestUrl($client, "https://www.filmaffinity.com/es/$id.html");
        return $this->processItem($crawler);
    }


    public function processLetter($letter, $explicitPage, $fullUpdate)
    {
        $startPage = $explicitPage ? $explicitPage : 1;
        $client = new Client();
        $crawler = $this->requestUrl($client, "https://www.filmaffinity.com/es/allfilms_" . $letter . "_" . $startPage . ".html");

        //Bucle que recorre las paginas hasta que no queden
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
        $client = new Client();
        $crawler = $this->requestUrl($client, "https://www.filmaffinity.com/es/allfilms_" . $letter . "_" . $page . ".html");

        //Recorremos las cards de la pagina
        $crawler->filter('.movie-card')->each(function($element) use($client, $fullUpdate) {
            $this->processCard($element, $client, 10, $fullUpdate);
        });
    }


    public function processCard($element, $client, $minVotes = 0, $fullUpdate)
    {
        //Scrapeamos movie card
        $card = $this->filmaffinity->getCard($element);

        //Serie o pelicula?
        $type = (preg_match('/\((Serie de TV)\)|\((Miniserie de TV)\)/', $card['fa_title'])) ? 'show' : 'movie';

        //Filtramos por numero minimo de votos
        if ($card['fa_count'] < $minVotes) return;
        
        //Solo actualizamos si ya existe (a no ser que se especifique la opcion fullUpdate)
        if (!$fullUpdate) {
            if ($this->genericRepository->checkIfMovieExist($card['fa_id'])) {
                $this->genericRepository->update($card);
                $this->output->message( $card['fa_title'] . " : Ya existe, la actualizamos", false);
                return;
            }
        }
        
        //click y entramos
        $crawler = $client->click($card['href']->link());
        $this->processItem($crawler);
        $this->output->message($card['fa_title'] . " : Entramos en la pagina de filmaffinity", false);
    }    

    /*
        processItem 
        Funcion: Recopila datos de Fa y Tmdb de un item y almacenamos
        Retorna: true o false
    */
    public function processItem($crawler)
    {
		//Scrapeamos fa
        $faData = $this->filmaffinity->getMovie($crawler);
        if ($faData['response'] == false) {
            $this->output->message($faData['message'], $faData['revision'], 'error');
            return false;
        }

        //datos de tmdb
        $tmData = $this->themoviedb->getMovie($faData);
        if ($tmData['response'] == false) {
            $this->output->message($tmData['message'], $tmData['log'], 'error');
            return false;
        } 

        //recopilamos toda la info
        $fullData = array_merge($faData, $tmData);
        $fullData['popularity'] = $this->algorithm->popularity($fullData['fa_year'], $fullData['tm_last_year'], $faData['fa_count'], $faData['fa_type']);

        //almacenamos y finalizamos
        $store = $this->itemRepository->run($fullData);
        $this->output->message($fullData['fa_title'] . " : Guardada ok en base de datos como " . $store['status'], false, 'comment'); 
        return true;
    }

    public function requestUrl($client, $url)
    {
        $crawler = $client->request('GET', $url);
        if ($client->getResponse()->getStatus() !== 200) {
            $this->output->message("Error: la url generada no es válida: $url", true, 'error');
            abort(404);
        } 
        $this->output->message("Entramos en $url", false);
        return $crawler;
    }

    public function runSeasons($id, $tmId)
    {
        $response = $this->themoviedb->setSeasons($tmId);
        if ($response) {
            $this->itemRepository->processSeasons($id, $response);
            $last = 0;
            foreach ($response as $item) {
                if ($item->season_number > $last) $last = $item->season_number;
            }
            return $last;
        }
        return false;
        
    }
    
}


