<?php
namespace App\Library\Providers;

use Unirest\Request;
use App\Library\Format;
use App\Library\Output;
use App\Library\Providers\Imdb;
use App\Models\Movie;
use App\Models\Season;
use App\Models\Verified;
Use App\Library\Images;

class Themoviedb
{
	private $format;
	private $output;
	private $imdb;
	private $images;

    public function __Construct(Format $format, Output $output, Imdb $imdb, Images $images)
	{
		$this->format = $format;
		$this->output = $output;
		$this->imdb = $imdb;
		$this->images = $images;
	}

    public function getMovie($faData)
    {
		// Responde con array con keys: response, message, id(?), tm_title(?), tm_review(?)

		$getId = $this->getId($faData);
		//si no encuentra el id de tmdb
		if ($getId['response'] == false) {
			return $getId;
		}

		if ($faData['fa_type'] == 'show') {
			$tmData = $this->getShowData($getId['tm_id']);
			if ($tmData['response'] == false) return $tmData;
			$imData = $this->imdb->getMovie($tmData['im_id'], $tmData['tm_last_year']);
		} else {
			$tmData = $this->getMovieData($getId['tm_id']);
			if ($tmData['response'] == false) return $tmData;
			$imData = $this->imdb->getMovie($tmData['im_id'], $tmData['tm_year']);
		}
		
		return array_merge($getId, $tmData, $imData);

    }

    public function getId($faData)
    {
		// Responde con array con keys: response, message, y id(?)

        $faId = $faData['fa_id'];
        $faTitle = $faData['fa_title'];
        $faOriginal = $faData['fa_original'];
		$faYear = $faData['fa_year'];
		$faType = $faData['fa_type'];
		$method = 'getApi' . $faType . 'Id';
		
		$verify = Verified::where([['id_1', $faId], ['source_2', 'tm']])->first();
		if ($verify) {
			$data['response'] = true;
			$data['message'] = $faData['fa_title'] . " : Encontramos id de tmdb desde verificadas";
			$data['tm_id'] = $verify->id_2;
			return $data;
		}

		$response = $this->{$method}($faTitle, $faYear);
		if ($response->body->total_results > 0) {
			$data = $this->checkMatch($faId, $faTitle, $faOriginal, $faYear, $response, $faType);
			if ($data['response'] == True) {
				$data['message'] = $faData['fa_title'] . " : Encontramos id de tmdb a la primera (por titulo)";
				return $data;
			}
		}

		$response = $this->{$method}($faOriginal, $faYear);
		if ($response->body->total_results > 0) {
			$data = $this->checkMatch($faId, $faTitle, $faOriginal, $faYear, $response, $faType);
			if ($data['response'] == True) {
				$data['message'] = $faData['fa_title'] . " : Encontramos id de tmdb a la segunda (por original)";
				return $data;
			}
		}

		//lo volvemos a intentar con año -1
		$fwYear = $faYear - 1;
		$response = $this->{$method}($faTitle, $fwYear);
		if ($response->body->total_results > 0) {
			$data = $this->checkMatch($faId, $faTitle, $faOriginal, $faYear, $response, $faType);
			if ($data['response'] == True) {
				$data['message'] = $faData['fa_title'] . " : Encontramos id de tmdb a la tercera (por año -1)";
				return $data;
			}
		}

		//lo volvemos a intentar con año +1
		$frYear = $faYear + 1;
		$response = $this->{$method}($faTitle, $frYear);
		if ($response->body->total_results > 0) {
			$data = $this->checkMatch($faId, $faTitle, $faOriginal, $faYear, $response, $faType);
			if ($data['response'] == True) {
				$data['message'] = $faData['fa_title'] . " : Encontramos id de tmdb a la cuarta (por año +1)";
				return $data;
			}
		}

		$data['response'] = false;
		$data['message'] = $faData['fa_title'] . " " . $faData['fa_id'] . " " . $faData['fa_count'] . "v. : No encontramos id de tmdb tras 4 intentos";
		$data['log'] = ($faData['fa_count'] > 100) ? true : false; //para guardar o no en archivo log
    	return $data;
    }

    public function getApiMovieId($string, $year)
	{
		return Request::get('https://api.themoviedb.org/3/search/movie?api_key=' . env('TMDB_API_KEY') . '&query=' . urlencode($string) . '&year=' . $year . '&language=es');
	}

	public function getApiShowId($string, $year)
	{
		return Request::get('https://api.themoviedb.org/3/search/tv?api_key=' . env('TMDB_API_KEY') . '&language=es&query=' . urlencode($string) . '&page=1&first_air_date_year=' . $year);
	}

    
    // COMPARA DATOS DE FILMAFFINITY CON TMDB PARA VERIFICAR ID TMDB
	public function checkMatch($faId, $faTitle, $faOriginal, $faYear, $tmdb, $type)
	{
		// Responde con array con keys: response(true) y id, o response(false) y message

		$exactMatch = $match = [];
		$faTitle = $this->format->removeMarks(strtolower($faTitle));
		$faOriginal = $this->format->removeMarks(strtolower($faOriginal));
		
        //recorremos las peliculas de tmdb
		foreach ($tmdb->body->results as $result) {
			
			if ($type == 'show') {
				$tmTitle = $this->format->removeMarks(strtolower($result->name));
				$tmOriginal = $this->format->removeMarks(strtolower($result->original_name));
			} else {
				$tmTitle = $this->format->removeMarks(strtolower($result->title));
				$tmOriginal = $this->format->removeMarks(strtolower($result->original_title));
			}
			
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
		$faType = $faData['fa_type'];

		if ($faType == 'show') $response1 = $this->getApiShowId($faTitle, $faYear);
		else $response1 = $this->getApiMovieId($faTitle, $faYear);
		$movies1 = $response1->body->results;

		if ($faType == 'show') $response2 = $this->getApiShowId($faOriginal, $faYear);
		else $response2 = $this->getApiMovieId($faOriginal, $faYear);
		$movies2 = $response2->body->results;

		$fwYear = $faYear - 1;
		if ($faType == 'show') $response3 = $this->getApiShowId($faTitle, $fwYear);
		else $response3 = $this->getApiMovieId($faTitle, $fwYear);
		$movies3 = $response3->body->results;
		
		$frYear = $faYear + 1;
		if ($faType == 'show') $response4 = $this->getApiShowId($faTitle, $frYear);
		else $response4 = $this->getApiMovieId($faTitle, $frYear);
		$movies4 = $response3->body->results;

		$allMovies = array_unique(array_merge($movies1, $movies2, $movies3, $movies4), SORT_REGULAR);

		if (empty($allMovies)) return [];

		if ($details == false) {
			return $allMovies;
		} else {
			if (!$more) {
				$allMovies = array_slice($allMovies, 0, 5);
			}
			if ($faType == 'show') {
				foreach ($allMovies as $movie) {
					$allMoviesDetails[] = $this->getShowData($movie->id);
				}
			} else {
				foreach ($allMovies as $movie) {
					$allMoviesDetails[] = $this->getMovieData($movie->id);
				}
			}
			return $allMoviesDetails;
		}



	}

	public function getMovieData($id)
	{
		// LLAMADA AL API DE TMDB PARA EL RESTO DE DATOS
		$tmdb = Request::get('https://api.themoviedb.org/3/movie/' . $id . '?api_key=' . env('TMDB_API_KEY') . '&language=es&append_to_response=credits');
		if ($tmdb->code != 200) return ['response' => false, 'message' => 'error ' . $tmdb->code];

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
		$data['tm_last_year'] = null;
		$data['tm_duration'] = $tmdb->body->runtime;
		$data['tm_countries'] = $tmdb->body->production_countries;
		$data['tm_type'] = 'movie';
		$data['response'] = true;
		return $data;
	}

	public function getShowData($id)
	{
		// LLAMADA AL API DE TMDB PARA EL RESTO DE DATOS
		$tmdb = Request::get('https://api.themoviedb.org/3/tv/' . $id . '?api_key=' . env('TMDB_API_KEY') . '&language=es&append_to_response=external_ids');
		if ($tmdb->code != 200) return ['response' => false, 'message' => 'error ' . $tmdb->code];

		$data['tm_id'] = $tmdb->body->id; //int
		$data['genres'] = $tmdb->body->genres; //object
		$data['im_id'] = $tmdb->body->external_ids->imdb_id; //string
		$data['tm_review'] = $tmdb->body->overview; //string
		$data['poster'] = $tmdb->body->poster_path; //string
		$data['background'] = $tmdb->body->backdrop_path; //string
		$data['tm_title'] = $tmdb->body->name; //string
		$data['tm_original'] = $tmdb->body->original_name; //string
		$tmFirstYear = explode('-', $tmdb->body->first_air_date); //string
		$data['tm_year'] = $tmFirstYear[0]; //string
		$tmLastYear = explode('-', $tmdb->body->last_air_date); //string
		$data['tm_last_year'] = $tmLastYear[0]; //string
		if (empty($data['tm_last_year'])) $data['tm_last_year'] = null;
		$data['tm_countries'] = $tmdb->body->origin_country; //array
		$data['tm_number_of_seasons'] = $tmdb->body->number_of_seasons; //int
		$data['tm_type'] = 'show';
		$data['tm_seasons'] = $tmdb->body->seasons;
		$data['response'] = true;
		return $data;
	}

	public function updateTmdbGenres()
	{
		$response1 = Request::get('https://api.themoviedb.org/3/genre/movie/list?api_key=' . env('TMDB_API_KEY') . '&language=es-ES');
		$response2 = Request::get('https://api.themoviedb.org/3/genre/tv/list?api_key=' . env('TMDB_API_KEY') . '&language=es-ES');
		
		//juntamos y eliminamos dubplicados
		$response = array_merge($response1->body->genres, $response2->body->genres);
		$response = array_unique($response, SORT_REGULAR);
		return $response;
	}
	
	
    public function updateBackgrounds()
    {
		Movie::where('id', '>', '41984')->chunk(100, function($movies) {
			foreach ($movies as $movie) {
				$tmData = $this->getMovieData($movie->tm_id);
				$saveBackground = $this->images->saveBackground($tmData['background'], $movie->slug);
				if ($saveBackground) $movie->check_background = 1;
				else $movie->check_background = 0;
				$movie->save();
			}
		});/*
		$movies = Movie::take(15)->get();
		foreach ($movies as $movie) {
			$tmData = $this->getMovieData($movie->tm_id);
			$saveBackground = $this->images->saveBackground($tmData['background'], $movie->slug, $source);
			if ($saveBackground) $movie->check_background = 1;
			else $movie->check_background = 0;
			$movie->save();
		}*/
	}

	public function setAllSeasons()
	{
		Movie::where([['id', '>', '43255'], ['type', 'show']])->chunkById(100, function($movies) {
			foreach ($movies as $movie) {
				$seasons = $this->getShowData($movie->tm_id);
				if ($seasons['response'] == false) {
					$this->output->message( "$movie->id $movie->title $movie->type : El request de tmdb da error, revisar si no corresponde el tipo", true, 'error');
					continue;
				}
				$seasons = $seasons['tm_seasons'];
				Season::where('movie_id', $movie->id)->delete();
				$seasonsArray = [];
				foreach($seasons as $key => $season) {
					$seasonsArray[$key]['movie_id'] = $movie->id;
					$seasonsArray[$key]['number'] = $season->season_number;
					$seasonsArray[$key]['year'] = substr($season->air_date, 0, 4);
					$seasonsArray[$key]['episodes'] = $season->episode_count;
					$seasonsArray[$key]['name'] = $season->name;
				}
				Season::insert($seasonsArray);
				$this->output->message( $movie->id . " : guardamos", false);
			}
		});
	}

	public function setSeasons($id)
	{
		$response = $this->getShowData($id);
		if (!$response['response']) {
			$this->output->message( "tmdb: $id da error al responder el api al intentar descargar seasons", true, 'error');
			return false;
		}
		return $response['tm_seasons'];

	}

	
}