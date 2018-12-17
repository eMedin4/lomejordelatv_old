<?php
namespace App\Library;

use Goutte\Client;
use App\Library\Repository;
use App\Library\Output;
use App\Library\Providers\FilmAffinity;
use App\Library\Providers\Themoviedb;

class MixMovies
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
        $this->themoviedb->updateAllMovieGenres();
	}

	public function setFromFaId( $source, $faid )
	{

		//url valida
		$url = 'https://www.filmaffinity.com/es/' . $faid . '.html';
		$client = new Client();
		$crawler = $client->request('GET', $url);
		if ($client->getResponse()->getStatus() !== 200) {
			$this->output->message("Error: la url generada no es válida: $url", true, $source);
			return;
		}
		$this->output->message("Entramos en $url", false, $source);

		//datos de filmaffinity
		$faData = $this->filmaffinity->getMovie($crawler, $minDuration = 0); //force: fuerza a descargar aunque duracion sea inferior a 30

		if ($faData['response'] == false) {
			$this->output->message($faData['message'], $faData['revision'], $source);
			return;
		}

		$this->output->message("Scaper Filmaffinity ok: " . $faData['fa_title'], false, $source);

		//datos de tmdb
		$tmData = $this->themoviedb->getMovie($faData, $source);

		if ($tmData['response'] == false) {
			$this->output->message($tmData['message'], true, $source);
			return;
		}

		$this->output->message("Datos de Themoviedb ok: " . $faData['fa_title'], false, $source);

		$fullData = array_merge($faData, $tmData);
		$store = $this->repository->storeItem($fullData, $source, 'movie');
		if ($store['status'] == 'updated') {
			$this->output->message("Actualizada en base de datos con id " . $store['id'], false, $source);
			return $store;
		} elseif ($store['status'] == 'created') {
			$this->output->message("Guardada en base de datos con id " . $store['id'], false, $source);
			return $store;
		}
		$this->output->message("Hay algún problema al guardar en base de datos", false, $source);
		return false;
		

	}

    public function setFromLetter($source, $letter, $firstPage, $totalPages)
    {

		//url valida
		$url = 'https://www.filmaffinity.com/es/allfilms_' . $letter . '_' . $firstPage . '.html';
		$client = new Client();
		$crawler = $client->request('GET', $url);
		if ($client->getResponse()->getStatus() !== 200) {
			$this->output->message("Error: la url generada no es válida: $url", true, $source);
			return;
		} 
		$this->output->message("Entramos en $url", false, $source);

        for ($i=1; $i<=$totalPages; $i++) {

			$crawler->filter('.movie-card')->each(function($element) use($client, $source) {
				$this->processCard($element, $client, $source, 10, 30);
			});

			//Avanzamos página
			if ($crawler->filter('.pager .current')->nextAll()->count()) {
                $upPage = $crawler->filter('.pager .current')->nextAll()->link();
                $crawler = $client->click($upPage);             
            //Si no hay más páginas salimos y terminamos
            } else {
            	break;
			}	
		}
	}
	
    public function FilmaffinityNetflixNew($source)
    {

		//url valida
		$url = 'https://www.filmaffinity.com/es/rdcat.php?id=new_netflix';
		$client = new Client();
		$crawler = $client->request('GET', $url);
		if ($client->getResponse()->getStatus() !== 200) {
			$this->output->message("Error: la url generada no es válida: $url", true, $source);
			return;
		} 

		for ($i=0; $i<20; $i++) {

			$this->output->message("Entramos en " . $client->getRequest()->getUri(), false, $source);

			$crawler->filter('.movie-card')->each(function($element) use($client, $source) {
				$this->processCard($element, $client, $source, 0, 0, $style = 'alternative');
			});

			$upPage = $crawler->filter('.prev-date-cat')->parents()->link();
			$crawler = $client->click($upPage);             

		}
	}
	
	public function FilmaffinityNetflixUpcoming($source)
	{
		//url valida
		$url = 'https://www.filmaffinity.com/es/rdcat.php?id=upc_netflix';
		$client = new Client();
		$crawler = $client->request('GET', $url);
		if ($client->getResponse()->getStatus() !== 200) {
			$this->output->message("Error: la url generada no es válida: $url", true, $source);
			return;
		} 

		$this->output->message($client->getRequest()->getUri() . " : Entramos en la url.", false, $source);

		$crawler->filter('.movie-card')->each(function($element) use($client, $source) {
			$this->processCard($element, $client, $source, 0, 0, $style = 'alternative');
		});
	}


	public function processCard($element, $client, $source, $minVotes = 0, $minDuration = 0, $style = 'standard')
	{
		//Scrapeamos movie card
		$card = $this->filmaffinity->getCard($element, $style);

		//Anolamos si tiene menos de 10 votos
		if ($card['fa_count'] < $minVotes) return;
	
		//Anulamos si es una serie o un corto
		if (preg_match('(\(Serie de TV\)|\(C\)|\((Miniserie de TV)\)', $card['fa_title'])) return;

		//Anulamos si está en nuestra lista negra
		if (in_array($card['fa_id'], config('movies.unavailable'))) return;
			
		//Comprobamos si ya existe en nuestra db
		if ($this->repository->checkIfMovieExist($card['fa_id'])) {
			$this->repository->update($card);
			$this->output->message( $card['fa_title'] . " : Ya existe, la actualizamos", false, $source);
			return;
		}

		//click y entramos
		$crawler = $client->click($card['href']->link());
		$this->output->message($card['fa_title'] . " : Entramos en la pagina de filmaffinity", false, $source);
				
		//Scrapeamos
		$faData = $this->filmaffinity->getMovie($crawler, $minDuration);

		if ($faData['response'] == false) {
			$this->output->message($faData['message'], $faData['revision'], $source);
		} else {

			//datos de tmdb
			$tmData = $this->themoviedb->getMovie($faData, $source);

			if ($tmData['response'] == false) {
				$this->output->message($tmData['message'], true, $source);
			} else {
				$this->output->message($faData['fa_title'] . " : Datos de Themoviedb ok", false, $source);
				$fullData = array_merge($faData, $tmData);
				$this->repository->storeItem($fullData, $source, 'movie');
				$this->output->message($fullData['fa_title'] . " : Guardada ok en base de datos", false, $source);
			}
		}
	}
}
