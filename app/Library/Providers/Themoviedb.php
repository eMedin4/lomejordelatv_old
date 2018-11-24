<?php
namespace App\Library\Providers;

use Unirest\Request;
use App\Library\Format;
use App\Library\Output;
use App\Library\Providers\Imdb;
use App\Library\Repository;
use App\Models\Movie;
use App\Models\Verified;
Use App\Library\Images;

class Themoviedb
{
	private $format;
	private $output;
	private $imdb;
	private $repository;
	private $images;

    public function __Construct(Format $format, Output $output, Imdb $imdb, Repository $repository, Images $images)
	{
		$this->format = $format;
		$this->output = $output;
		$this->imdb = $imdb;
		$this->repository = $repository;
		$this->images = $images;
	}

    public function getMovie($faData, $source = 'browse')
    {
		// Responde con array con keys: response, message, id(?), tm_title(?), tm_review(?)

		$getId = $this->getId($faData);
		//si no encuentra el id de tmdb
		if ($getId['response'] == false) {
			return $getId;
		}
		$tmData = $this->getData($getId['tm_id']);

		if ($getId['verified_manually'] == false ) {
			$checkYears = $this->format->checkYears($faData['fa_year'], $tmData['tm_year'], $tolerance = 1);
			if ($checkYears == false) {
				$this->output->message($faData['fa_title'] . " : Aceptada con reparos: Los años no coinciden: fa " . $faData['fa_id'] . " -> " . $faData['fa_year'] . " y " . $getId['tm_id'] . " -> " . $tmData['tm_year'], true, $source);
			}
		}

		$imData = $this->imdb->getMovie($tmData['im_id'], $tmData['tm_year']);
		return array_merge($getId, $tmData, $imData);

    }

    public function getId($faData)
    {
		// Responde con array con keys: response, message, verified_manually y id(?)

        $faId = $faData['fa_id'];
        $faTitle = $faData['fa_title'];
        $faOriginal = $faData['fa_original'];
		$faYear = $faData['fa_year'];
		
		$verify = Verified::where([['id_1', $faId], ['source_2', 'tm']])->first();
		if ($verify) {
			$data['response'] = true;
			$data['message'] = $faData['fa_title'] . " : Encontramos id de tmdb desde verificadas";
			$data['verified_manually'] = true;
			$data['tm_id'] = $verify->id_2;
			return $data;
		}

		$response = $this->getApiId($faTitle, $faYear);
		if ($response->body->total_results > 0) {
			$data = $this->checkMatch($faId, $faTitle, $faOriginal, $faYear, $response);
			if ($data['response'] == True) {
				$data['verified_manually'] = false;
				$data['message'] = $faData['fa_title'] . " : Encontramos id de tmdb a la primera (por titulo)";
				return $data;
			}
		}

		$response = $this->getApiId($faOriginal, $faYear);
		if ($response->body->total_results > 0) {
			$data = $this->checkMatch($faId, $faTitle, $faOriginal, $faYear, $response);
			if ($data['response'] == True) {
				$data['verified_manually'] = false;
				$data['message'] = $faData['fa_title'] . " : Encontramos id de tmdb a la segunda (por original)";
				return $data;
			}
		}

		//lo volvemos a intentar con año -1
		$fwYear = $faYear - 1;
		$response = $this->getApiId($faTitle, $fwYear);
		if ($response->body->total_results > 0) {
			$data = $this->checkMatch($faId, $faTitle, $faOriginal, $faYear, $response);
			if ($data['response'] == True) {
				$data['verified_manually'] = false;
				$data['message'] = $faData['fa_title'] . " : Encontramos id de tmdb a la tercera (por año -1)";
				return $data;
			}
		}

		//lo volvemos a intentar con año +1
		$frYear = $faYear + 1;
		$response = $this->getApiId($faTitle, $frYear);
		if ($response->body->total_results > 0) {
			$data = $this->checkMatch($faId, $faTitle, $faOriginal, $faYear, $response);
			if ($data['response'] == True) {
				$data['verified_manually'] = false;
				$data['message'] = $faData['fa_title'] . " : Encontramos id de tmdb a la cuarta (por año +1)";
				return $data;
			}
		}

		$data['response'] = false;
		$data['message'] = $faData['fa_title'] . " : No encontramos id de tmdb tras 4 intentos";
    	return $data;
    }

    public function getApiId($string, $year)
	{
		return Request::get('https://api.themoviedb.org/3/search/movie?api_key=' . env('TMDB_API_KEY') . '&query=' . urlencode($string) . '&year=' . $year . '&language=es');
	}

    
    // COMPARA DATOS DE FILMAFFINITY CON TMDB PARA VERIFICAR ID TMDB
	public function checkMatch($faId, $faTitle, $faOriginal, $faYear, $tmdb)
	{
		// Responde con array con keys: response(true) y id, o response(false) y message

		$exactMatch = $match = [];
		$faTitle = $this->format->removeMarks(strtolower($faTitle));
        $faOriginal = $this->format->removeMarks(strtolower($faOriginal));
		
        //recorremos las peliculas de tmdb
		foreach ($tmdb->body->results as $result) {
			
			$tmTitle = $this->format->removeMarks(strtolower($result->title));
			$tmOriginal = $this->format->removeMarks(strtolower($result->original_title));
			
            //si coincide titulo y original es exactmatch
			if (($faTitle == $tmTitle) && ($faOriginal == $tmOriginal)) {
				array_push($exactMatch, $result->id);
			}
			
            //si coincide alguna combinacion de titulos y originales entre ellos es match
			if ($faTitle == $tmTitle) array_push($match, $result->id);
			if ($faOriginal == $tmTitle) array_push($match, $result->id);
			if ($faTitle == $tmOriginal) array_push($match, $result->id);
			if ($faOriginal == $tmOriginal) array_push($match, $result->id);
		}
		
        //si hay 1 y solo 1 exactmatch 
		if (count($exactMatch) == 1) {
			$exactMatchResponse = True;
			$exactMatchId = $exactMatch[0];
		} else {
			$exactMatchResponse = False;
			$exactMatchId = '';
		}

        //si todos los match pertenecen a la misma película son iguales entre si o diferentes
		$matchId = '';
		if (count($match) == 0) {
			$matchResponse = False;
		} else {
			$matchId = current($match);
			$matchResponse = True;
			foreach ($match as $val) {
				if ($matchId !== $val) {
					$matchResponse = False;
				}
			}
		}
		//dd($faTitle, $faOriginal, $exactMatchResponse, $exactMatch, $matchResponse, $match, $tmdb);

		//RESPUESTAS
		if (($exactMatchResponse == True) && ($matchResponse == True) && ($exactMatchId == $matchId)) {
            return ['response' => True, 'tm_id' => $exactMatchId];
            
		} elseif (($exactMatchResponse == True) && ($matchResponse == True) && ($exactMatchId != $matchId)) {
            return ['response' => False, 'message' => 'Rechazada, checkTmdbId: exactMatch ok, match ok, pero ids entre ellos no coinciden'];
            
		} elseif (($exactMatchResponse == True) && ($matchResponse == False)) {
            return ['response' => True, 'tm_id' => $exactMatchId];
            
		} elseif (($exactMatchResponse == False) && ($matchResponse == True)) {
            return ['response' => True, 'tm_id' => $matchId];
            
		} else {
			return ['response' => False, 'message' => 'No encontramos match'];
		}
	}

	public function getAllResults($faData, $details, $more)
	{
		
        $faId = $faData['fa_id'];
        $faTitle = $faData['fa_title'];
        $faOriginal = $faData['fa_original'];
		$faYear = $faData['fa_year'];

		$response1 = $this->getApiId($faTitle, $faYear);
		$movies1 = $response1->body->results;

		$response2 = $this->getApiId($faOriginal, $faYear);
		$movies2 = $response2->body->results;

		$fwYear = $faYear - 1;
		$response3 = $this->getApiId($faTitle, $fwYear);
		$movies3 = $response3->body->results;
		
		$frYear = $faYear + 1;
		$response4 = $this->getApiId($faTitle, $frYear);
		$movies4 = $response3->body->results;

		$allMovies = array_unique(array_merge($movies1, $movies2, $movies3, $movies4), SORT_REGULAR);

		if ($details == false) {
			return $allMovies;
		} else {
			if (!$more) {
				$allMovies = array_slice($allMovies, 0, 5);
			}
			foreach ($allMovies as $movie) {
				$allMoviesDetails[] = $this->getData($movie->id);
			}
			return $allMoviesDetails;
		}



	}

	public function getData($id)
	{
		// LLAMADA AL API DE TMDB PARA EL RESTO DE DATOS
		$tmdb = Request::get('https://api.themoviedb.org/3/movie/' . $id . '?api_key=' . env('TMDB_API_KEY') . '&language=es&append_to_response=credits');
		$data['tm_id'] = $tmdb->body->id;
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
		$data['tm_countries'] = $tmdb->body->production_countries;
		return $data;
	}

	public function updateAllGenres()
    {
		$response = Request::get('https://api.themoviedb.org/3/genre/movie/list?api_key=' . env('TMDB_API_KEY') . '&language=es-ES');
		$apiGenres = $this->repository->updateAllGenres($response->body->genres);
	}
	
    public function updateBackgrounds($source)
    {
        Movie::where('id', '>', '10070')->chunk(100, function($movies) use($source) {
            foreach ($movies as $movie) {
			$tmData = $this->getData($movie->tm_id);
			$saveBackground = $this->images->saveBackground($tmData['background'], $movie->slug, $source);
			if ($saveBackground) $movie->check_background = 1;
			else $movie->check_background = 0;
			$movie->save();
		}
		});/*
		$movies = Movie::take(15)->get();
		foreach ($movies as $movie) {
			$tmData = $this->getData($movie->tm_id);
			$saveBackground = $this->images->saveBackground($tmData['background'], $movie->slug, $source);
			if ($saveBackground) $movie->check_background = 1;
			else $movie->check_background = 0;
			$movie->save();
		}*/
    }
}