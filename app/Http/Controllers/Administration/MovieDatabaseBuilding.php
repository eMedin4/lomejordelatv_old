<?php
namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request as LaravelRequest;
use Goutte\Client;
use App\Http\Controllers\Administration\Repository;
use App\Http\Controllers\Administration\Format;
use App\Http\Controllers\Administration\Algorithm;
use Unirest\Request;

class MovieDatabaseBuilding extends Controller
{

    private $repository;
	private $format;
	private $algorithm;

    public function __Construct(Repository $repository, Format $format, Algorithm $algorithm)
	{
		$this->repository = $repository;
		$this->format = $format;
		$this->algorithm = $algorithm;
		ini_set('memory_limit', '-1');
        set_time_limit(28800);
        $this->updateAllGenres();
	}

	public function filmaffinityId(LaravelRequest $laravelRequest)
	{

		//MODO DEBUG?
		$deb = $laravelRequest->input("debug");
		if (isset($deb)) {
			$deb = true;
			echo "<div style='font-size:14px; font-family: \"Roboto Mono\", monospace; margin: 20px'>";
		}
		else $deb = false;

		//CREAMOS LA URL CON LOS DATOS DEL FORMULARIO
		$faid = trim($laravelRequest->input('faid'));
		if (substr( $faid, 0, 4 ) !== "film") $faid = "film" . $this->format->integer($faid);
		$url = 'https://www.filmaffinity.com/es/' . $faid . '.html';
		$client = new Client();
		$crawler = $client->request('GET', $url);
		if ($client->getResponse()->getStatus() !== 200) {
			Log::channel('customErrors')->debug('filmaffinityId: La URL generada no es valida: ' . $url);
			return ('error la url generada no es válida');
		} 

		//SCRAPEAMOS PELÍCULA
		$movie = $this->getMovie($crawler, $deb);

		//VERIFICAMOS SI VIENE CON RESPONSE FAIL
		if ($movie['response'] == False) {
			echo 'Response: False' . '<br>' . 'Details: ' . $movie['details'];
			return;
		}

		//VERIFICACIÓN POR AÑO Y DURACIÓN (por duración no rechazamos, pero por año si)
		if (!isset($movie['data']['verifiedManually'])) {
			$checkYears = $this->format->checkIfNearly($movie['data']['fa_year'], $movie['data']['tm_year'], $tolerance = 1);
			if (!$checkYears['response']) {
				echo 'Rechazada: Los años no coinciden: fa ' . $movie['data']['fa_id'] . ': ' . $movie['data']['fa_year'] . ' y tm ' . $movie['data']['tm_id'] . ': ' . $movie['data']['tm_year'];
				Log::channel('customMovies')->debug('Rechazadas: Los años no coinciden: fa ' . $movie['data']['fa_id'] . ': ' . $movie['data']['fa_year'] . ' y tm ' . $movie['data']['tm_id'] . ': ' . $movie['data']['tm_year']);
				return;
			}
		}
		$checkDuration = $this->format->checkIfNearly($movie['data']['fa_duration'], $movie['data']['tm_duration'], $tolerance = 8);
		$movie['data']['reliable_duration'] = ($checkDuration['response'] && $checkDuration['diff'] < 3) ? true : false; //si la diferencia de duraciones es menor a 3 las damos como fiables (para comparar con movistar a posteriori)

		//SI VIENE CON RESPONSE TRUE GUARDAMOS
		if ($movie['response'] == True) {
			$this->repository->storeMovie($movie['data']);
			echo 'guardada con éxito: ' . $movie['data']['fa_id'] . '<br>';
		} else {
			echo 'llegamos al final pero no guardamos: ' . $movie['data']['fa_id'] . '<br>';
		}

	}

	public function filmaffinityMultipleIds(LaravelRequest $laravelRequest)
	{
		//MODO DEBUG?
		$deb = $laravelRequest->input("debug");
		if (isset($deb)) {
			$deb = true;
			echo "<div style='font-size:14px; font-family: \"Roboto Mono\", monospace; margin: 20px'>";
		}
		else $deb = false;

		//CREAMOS LA URL CON LOS DATOS DEL FORMULARIO
		$faids = explode(',', $laravelRequest->input('faids'));
		$client = new Client();
		foreach($faids as $faid) {
			if (substr( $faid, 0, 4 ) !== "film") $faid = "film" . $this->format->integer($faid);
			$url = 'https://www.filmaffinity.com/es/' . $faid . '.html';
			$crawler = $client->request('GET', $url);

			if ($client->getResponse()->getStatus() !== 200) {
				echo 'la url ' . $url . ' generada no es váilda' . '<br>';
				continue;
			} 

			//SCRAPEAMOS PELÍCULA
			$movie = $this->getMovie($crawler, $deb);

			//VERIFICAMOS SI VIENE CON RESPONSE FAIL
			if ($movie['response'] == False) {
				echo 'Response: False. Details: ' . $movie['details'];
				continue;
			}

			//VERIFICACIÓN POR AÑO Y DURACIÓN (por duración no rechazamos, pero por año si)
			if (!isset($movie['data']['verifiedManually'])) {
				$checkYears = $this->format->checkIfNearly($movie['data']['fa_year'], $movie['data']['tm_year'], $tolerance = 1);
				if (!$checkYears['response']) {
					echo 'Rechazada: Los años no coinciden: fa ' . $movie['data']['fa_id'] . ': ' . $movie['data']['fa_year'] . ' y tm ' . $movie['data']['tm_id'] . ': ' . $movie['data']['tm_year'];
					Log::channel('customMovies')->debug('Rechazadas: Los años no coinciden: fa ' . $movie['data']['fa_id'] . ': ' . $movie['data']['fa_year'] . ' y tm ' . $movie['data']['tm_id'] . ': ' . $movie['data']['tm_year']);
					continue;
				}
			}
			$checkDuration = $this->format->checkIfNearly($movie['data']['fa_duration'], $movie['data']['tm_duration'], $tolerance = 8);
			$movie['data']['reliable_duration'] = ($checkDuration['response'] && $checkDuration['diff'] < 3) ? true : false; //si la diferencia de duraciones es menor a 3 las damos como fiables (para comparar con movistar a posteriori)

			//SI VIENE CON RESPONSE TRUE GUARDAMOS
			if ($movie['response'] == True) {
				$this->repository->storeMovie($movie['data']);
				echo 'guardada con éxito: ' . $movie['data']['fa_id'] . '<br>';
			} else {
				echo 'llegamos al final pero no guardamos: ' . $movie['data']['fa_id'] . '<br>';
			}
		}
	}

    public function filmaffinityAlphabetically(LaravelRequest $laravelRequest)
    {
		//MODO DEBUG?
		$deb = $laravelRequest->input("debug");
		if (isset($deb)) {
			$deb = true;
			echo "<div style='font-size:14px; font-family: \"Roboto Mono\", monospace; margin: 20px'>";
		}
		else $deb = false;

		//x

		//CREAMOS LA URL CON LOS DATOS DEL FORMULARIO
		if ($laravelRequest->input("letter")) $letter = $laravelRequest->input('letter');
		else return 'Necesitamos una letra';
		if ($laravelRequest->input('first-page')) $firstPage = $laravelRequest->input('first-page');
		else $firstPage = '1';
		$url = 'https://www.filmaffinity.com/es/allfilms_' . $letter . '_' . $firstPage . '.html';
		$client = new Client();
		$crawler = $client->request('GET', $url);
		if ($client->getResponse()->getStatus() !== 200) {
			Log::channel('customErrors')->debug('filmAffinityAlphabetically: La URL generada no es valida: ' . $url);
			return ('error la url generada no es válida');
		} 

		//NÚMERO TOTAL DE PÁGINAS A SCRAPEAR
		$lastPageCrawler = $crawler->filter('.pager')->children();
		$count = $lastPageCrawler->count();
		$lastPage = $lastPageCrawler->eq($count - 1)->text();
		if ($lastPage == ">>") {
			$lastPage = $lastPageCrawler->eq($count - 2)->text();
		}
		$totalPages = $lastPage - $firstPage + 1;
		if (($laravelRequest->input('total-pages')) && ($laravelRequest->input('total-pages') < $totalPages)) {
			$totalPages = $laravelRequest->input('total-pages');
		}

        for ($i=1; $i<=$totalPages; $i++) {

			//SCRAPEAMOS PÁGINA
			echo 'pagina: ' . $i . '<br>';
			$crawler->filter('.movie-card')->each(function($element) use($client, $deb) {

				//SCRAPEAMOS MOVIE CARD
				$card = $this->cardScrap($element);

				//SI TIENE 10 O MENOS VOTOS SALIMOS
				if ($card['fa_count'] < 10) return;
				
				//SI ES UNA SERIE O UN CORTO SALIMOS
				if (preg_match('(\(Serie de TV\)|\(C\))', $card['fa_title'])) return;

				//SI ESTÁ EN NUESTRA LISTA DE UNAVAILABLE SALIMOS
				if (in_array($card['fa_id'], config('movies.unavailable'))) return;
				
				//SI YA EXISTE EN NUESTRA DB
				if ($this->repository->checkIfExist($card['fa_id'])) {
					$this->repository->update($card);
					return;
				}

				//CLICK Y ENTRAMOS
				$crawler = $client->click($card['href']->link());
				echo $card['fa_id'] . '<br>';
					
				//SCRAPEAMOS PELICULA
				$movie = $this->getMovie($crawler, $deb);

				//VERIFICAMOS SI VIENE CON RESPONSE FAIL
				if ($movie['response'] == False) return;

				//VERIFICACIÓN POR AÑO Y DURACIÓN
				if (!isset($movie['data']['verifiedManually'])) {
					$checkYears = $this->format->checkIfNearly($movie['data']['fa_year'], $movie['data']['tm_year'], $tolerance = 1);
					if (!$checkYears['response']) {
						echo 'Los años no coinciden: ' . $movie['data']['fa_id'] . ': ' . $movie['data']['fa_year'] . ' y ' . $movie['data']['tm_id'] . ': ' . $movie['data']['tm_year'];
						Log::channel('customMovies')->debug('Los años no coinciden: ' . $movie['data']['fa_id'] . ': ' . $movie['data']['fa_year'] . ' y ' . $movie['data']['tm_id'] . ': ' . $movie['data']['tm_year']);
						return;
					}
				}
				$checkDuration = $this->format->checkIfNearly($movie['data']['fa_duration'], $movie['data']['tm_duration'], $tolerance = 8);
				$movie['data']['reliable_duration'] = ($checkDuration['response'] && $checkDuration['diff'] < 3) ? true : false; //si la diferencia de duraciones es menor a 3 las damos como fiables (para comparar con movistar a posteriori)


				//SI VIENE CON RESPONSE TRUE GUARDAMOS
				if ($movie['response'] == True) {
					$this->repository->storeMovie($movie['data']);
				} else {
					Log::channel('customMovies')->debug('llegamos al final pero no guardamos: ' . $movie['data']['fa_id']);
					echo 'llegamos al final pero no guardamos: ' . $movie['data']['fa_id'] . '<br>';
				}

			});

			//AVANZAMOS PÁGINA
			if ($crawler->filter('.pager .current')->nextAll()->count()) {
                $upPage = $crawler->filter('.pager .current')->nextAll()->link();
                $crawler = $client->click($upPage);             
            //SI YA NO HAY MAS SALIMOS DEL BUCLE FOR
            } else {
            	break;
			}
			
		}

		Log::channel('customErrors')->debug(__METHOD__ . 'Scrapeamos ' . $letter . ' desde ' . $firstPage . ' hasta ' . $totalPages);
        
    }

    public function cardScrap($element)
	{
		$result['href']  = $element->filter('.movie-card .mc-title a'); 
		$result['fa_id'] = $this->format->faId($result['href']->attr('href'));
		$result['fa_title'] = $element->filter('.movie-card .mc-title a')->text(); 
		$result['fa_rat'] = $this->format->score($element->filter('.avgrat-box')->text());
		$result['fa_count'] = $this->format->integer($this->format->getElementIfExist($element, '.ratcount-box', 0));
		return $result;
    }
    

    /*
    |--------------------------------------------------------------------------
    |
    |   SCRAPEO DE UNA PELÍCULA
    |
    |--------------------------------------------------------------------------
    */

    public function getMovie($crawler, $deb = False) 
	{

		/*
	    |--------------------------------------------------------------------------
	    | EN FILMAFFINITY
	    |--------------------------------------------------------------------------
	    */

		// fa_id
		if ($crawler->filter('.ntabs a')->count()) {
			$data['fa_id'] = $this->format->faId($crawler->filter('.ntabs a')->eq(0)->attr('href'));
		} else {
			Log::channel('customErrors')->debug('No se encuentra ID de filmaffinity en la clase .ntabs a: ' . $crawler->getUri());
			return ['response' => False, 'details' => 'No se encuentra ID de filmaffinity en la clase .ntabs a: ' . $crawler->getUri()];
		}

		// title
		$data['fa_title'] = $crawler->filter('#main-title span')->text();
		$data['fa_title'] = $this->format->removeString($data['fa_title'], '(TV)');

		//Construimos array con los datos de la table(no tienen ids)
        $table = $crawler->filter('.movie-info dt')->each(function($element) {
            return [$element->text() => $element->nextAll()->text()];
		});
		
		//Devuelve un array de arrays, lo convertimos a array normal
        foreach ($table as $key => $value) { 
            $table2[key($value)] = current($value);
        }

        //Datos de la tabla de fa
		$data['fa_original'] = $this->format->removeString($this->format->cleanData($this->format->getValueIfExist($table2, 'Título original')), 'aka');
		$data['fa_original'] = $this->format->removeString($data['fa_original'], '(TV)');
		$data['fa_year'] = $this->format->getValueIfExist($table2, 'Año');
		$data['fa_duration'] = $this->format->integer($this->format->getValueIfExist($table2, 'Duración'));
		$data['country'] = $this->format->cleanData($this->format->getValueIfExist($table2, 'País'));
		$data['fa_review'] = $this->format->removeString($this->format->getValueIfExist($table2, 'Sinopsis'), '(FILMAFFINITY)');

		//modo debug
		if ($deb) {
			echo '<h3>' . 'Datos Filmaffinity' . '</h3>';
			foreach ($data as $key => $value) {
				if ($key != 'fa_review') echo $key . ': <b>' . $value . '</b><br>';
			}
		}

		//Si dura menos de 30 minutos pero existe duración anulamos
		if (($data['fa_duration'] != 0) && ($data['fa_duration'] < 30)) return ['response' => False, 'details' => 'Rechazada: Duración inferior a 30 minutos'];

		// fa_rat y fa_count
		$faRat = $this->format->float($this->format->getElementIfExist($crawler, '#movie-rat-avg', NULL));
		$faCount = $this->format->getElementIfExist($crawler, '#movie-count-rat span', NULL);
		$faCount = $this->format->integer(str_replace('.', '', $faCount));
		if ($faRat && $faCount) {
			$data['fa_rat'] = $faRat;
			$data['fa_count'] = $faCount;
			$data['fa_popularity'] = $this->algorithm->popularity($data['fa_year'], $faCount, 'fa');
		} else {
			$data['fa_rat'] = $data['fa_count'] = $data['fa_popularity'] = NULL;
		}

		//Si no tiene año o duración y tiene menos de 300 votos anulamos
		if (($data['fa_year'] == null) || ($data['fa_duration'] == null)) {
			if (($data['fa_count'] == null) || ($data['fa_count'] < 300)) {
				return ['response' => False, 'details' => 'Rechazada: No hay year o duration en fa pero tiene menos de 300 votos'];
			}
		}

		/*
	    |--------------------------------------------------------------------------
	    | THEMOVIEDB
	    |--------------------------------------------------------------------------
	    */

	    //BUSCAMOS EN TMDB CON LOS DATOS DE FILMAFFINITY
		$searchTmdbId = $this->searchTmdbId($data['fa_id'], $data['fa_title'], $data['fa_original'], $data['fa_year'], $deb);

		//SI NO ENCONTRAMOS LA PELÍCULA EN TMDB
		if ($searchTmdbId['response'] == false) {
			if ($deb) echo 'getMovie -> No encontramos la película en TMDB' . '<br>';
			if ($data['fa_count'] > 300) {
				Log::channel('customMovies')->debug('getMovie -> Rechazamos: No encontramos la película en TMDB y en FA tiene más de 300 votos: ' . $data['fa_id']);
				return ['response' => false, 'details' => 'getMovie -> Rechazamos: No encontramos la película en TMDB y en FA tiene más de 300 votos: ' . $data['fa_id']];
			} else {
				return ['response' => false, 'details' => 'getMovie -> Rechazamos: No encontramos la película en TMDB pero en FA tiene menos de 300 votos: ' . $data['fa_id']];
			}
		}

		$data['tm_id'] = $searchTmdbId['id'];
		if (isset($searchTmdbId['verifiedManually'])) $data['verifiedManually'] = True;
		
	    // LLAMADA AL API DE TMDB PARA EL RESTO DE DATOS
		$tmdb = Request::get('https://api.themoviedb.org/3/movie/' . $data['tm_id'] . '?api_key=' . env('TMDB_API_KEY') . '&language=es&append_to_response=credits');
		$data['credits'] = $tmdb->body->credits;
		$data['genres'] = $tmdb->body->genres;	
		$data['im_id'] = $tmdb->body->imdb_id;
		$data['tm_review'] = $tmdb->body->overview;
		$data['poster'] = $tmdb->body->poster_path;
		$data['background'] = $tmdb->body->backdrop_path;
		$data['tm_title'] = $tmdb->body->title;
		$data['tm_original'] = $tmdb->body->original_title;
		$tmYear = explode('-', $tmdb->body->release_date);
		$data['tm_year'] = $tmYear[0];
		$data['tm_duration'] = $tmdb->body->runtime;

		//modo debug
		if ($deb) {
			echo '<h3>' . 'Datos TMDB:' . '</h3>';
			echo 'tm_id: <b>' . $data['tm_id'] . '</b><br>';
			echo 'tm_title: <b>' . $data['tm_title'] . '</b><br>';
			echo 'tm_original: <b>' . $data['tm_original'] . '</b><br>';
			echo 'tm_year: <b>' . $data['tm_year'] . '</b><br>';
			echo 'tm_duration: <b>' . $data['tm_duration'] . '</b><br>';
		}

		/*
	    |--------------------------------------------------------------------------
	    | IMDB
	    |--------------------------------------------------------------------------
	    */

	    /* SI NO EXISTE ID DE IMDB TERMINAMOS*/
	    if (is_null($data['im_id'])) {
			$data['im_rat'] = $data['im_count'] = $data['im_popularity'] = $data['rt_rat'] = null;
			return ['response' => true, 'data' => $data];
		} 
		// Llamamos al API de omdb
		$imdb = Request::get('http://www.omdbapi.com/?i=' . urlencode($data['im_id']) . '&plot=full&apikey=' . env('OMDB_API_KEY'));
		
		if ($imdb->body->Response == "False") {
			$data['im_rat'] = $data['im_count'] = $data['im_popularity'] = $data['rt_rat'] = null;
			return ['response' => true, 'data' => $data];
		}
		

		$imRat = $this->format->float($imdb->body->imdbRating);
		$imCount = $this->format->integer($imdb->body->imdbVotes);
		if ($imRat && $imCount && $imRat != 'N/A' && $imCount != 'N/A') {
			$data['im_rat'] = $imRat;
			$data['im_count'] = $imCount;
			$data['im_popularity'] = $this->algorithm->popularity($data['tm_year'], $imCount, 'im');
		} else {
			$data['im_rat'] = $data['im_count'] = $data['im_popularity'] = null;
		}

		if (empty($imdb->body->Ratings)) {
			$data['rt_rat'] = null;
		} else {
			foreach ($imdb->body->Ratings as $ratings) {
				if ($ratings->Source == 'Rotten Tomatoes') $data['rt_rat'] = $this->format->integer($ratings->Value);
				else $data['rt_rat'] = null;
			}
		}

		return ['response' => true, 'data' => $data];
    }


    /*
    |--------------------------------------------------------------------------
    |
    |   API TMDB
    |
    |--------------------------------------------------------------------------
    */

    public function searchTmdbId($faId, $faTitle, $faOriginal, $faYear, $deb)
	{

		if (array_key_exists($faId, config('movies.verified'))) {
			return ['response' => True, 'id' => config('movies.verified')[$faId], 'verifiedManually' => True]; //ID DE VERIFICADAS
		}

		$tmdb = $this->apiTmdbId($faTitle, $faYear);
		if ($tmdb->body->total_results > 0) {
			$algorithm = $this->algorithm->checkTmdbId($faId, $faTitle, $faOriginal, $faYear, $tmdb, $deb);
			if ($algorithm['response'] == True) {
				if ($deb) echo 'Encontramos el tmdb a la primera. ' . '<br>';
				return $algorithm;
			}
		}

		$tmdb = $this->apiTmdbId($faOriginal, $faYear);
		if ($tmdb->body->total_results > 0) {
			$algorithm = $this->algorithm->checkTmdbId($faId, $faTitle, $faOriginal, $faYear, $tmdb, $deb);
			if ($algorithm['response'] == True) {
				if ($deb) echo 'Encontramos el tmdb a la primera. ' . '<br>';
				return $algorithm;
			}
		}

		//lo volvemos a intentar con año -1
		$fwYear = $faYear - 1;
		$tmdb = $this->apiTmdbId($faTitle, $fwYear);
		if ($tmdb->body->total_results > 0) {
			$algorithm = $this->algorithm->checkTmdbId($faId, $faTitle, $faOriginal, $faYear, $tmdb, $deb);
			if ($algorithm['response'] == True) {
				if ($deb) echo 'searchTmdbId: Encontrada bucando por year-1 y no por year:' . '<br>';
				return $algorithm;
			}
		}

		//lo volvemos a intentar con año +1
		$frYear = $faYear + 1;
		$tmdb = $this->apiTmdbId($faTitle, $frYear);
		if ($tmdb->body->total_results > 0) {
			$algorithm = $this->algorithm->checkTmdbId($faId, $faTitle, $faOriginal, $faYear, $tmdb, $deb);
			if ($algorithm['response'] == True) {
				if ($deb) echo 'searchTmdbId: Encontrada bucando por year+1 y no por year:' . '<br>';
				return $algorithm;
			}
		}

    	return ['response' => False];
    }
    
    public function apiTmdbId($string, $year)
	{
		return Request::get('https://api.themoviedb.org/3/search/movie?api_key=' . env('TMDB_API_KEY') . '&query=' . urlencode($string) . '&year=' . $year . '&language=es');
	}
    

    public function updateAllGenres()
    {
    	$response = Request::get('https://api.themoviedb.org/3/genre/movie/list?api_key=' . env('TMDB_API_KEY') . '&language=es-ES');
		$apiGenres = $this->repository->updateAllGenres($response->body->genres);
    }

    public function stick()
    {
		$source1 = trim($laravelRequest->input('source1'));
		$id1 = trim($laravelRequest->input('id1'));
		$source2 = trim($laravelRequest->input('source2'));
		$id2 = trim($laravelRequest->input('id2'));
		if ($source1 == 'fa') {
			if (substr( $id1, 0, 4 ) !== "film") $faid = "film" . $this->format->integer($faid);
			$url = 'https://www.filmaffinity.com/es/' . $faid . '.html';
			$client = new Client();
			$crawler = $client->request('GET', $url);
			if ($client->getResponse()->getStatus() !== 200) {
				return redirect()->route('administration.dashboard')->with('message', 'error la url generada no es válida');
			}
			$card = $this->cardScrap($crawler);
			dd($card);
		}
    }
}
