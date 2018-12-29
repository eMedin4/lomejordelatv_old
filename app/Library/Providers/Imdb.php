<?php
namespace App\Library\Providers;

use Unirest\Request;
use App\Library\Format;
use App\Library\Algorithm;

class Imdb
{
	private $format;
	private $algorithm;

    public function __Construct(Format $format, Algorithm $algorithm)
	{
		$this->format = $format;
		$this->algorithm = $algorithm;
	}

    public function getMovie($id, $year)
    {
        // Responde con array con keys: response, data(siempre) y message
        
        //Comprobamos si existe
	    if (is_null($id)) {
			$data['im_rat'] = $data['im_count'] = $data['rt_rat'] = null;
			//$data['response'] = false;
			$data['message'] = 'No hay id de tmdb para buscar en imdb';
			return $data;
		} 
		//Llamamos al API
		$imdb = Request::get('http://www.omdbapi.com/?i=' . urlencode($id) . '&plot=full&apikey=' . env('OMDB_API_KEY'));
		
		if ($imdb->body->Response == "False") {
			$data['im_rat'] = $data['im_count'] = $data['rt_rat'] = null;
			//$data['response'] = false;
			$data['message'] = 'La busqueda en imdb devuelve error';
			return $data;
        }
        
        if (isset($imdb->body->imdbRating) && $imdb->body->imdbRating != 'N/A') {
            $data['im_rat'] = $this->format->float($imdb->body->imdbRating);
        } else {
            $data['im_rat'] = null;
        }
        if (isset($imdb->body->imdbVotes) && $imdb->body->imdbVotes != 'N/A') {
            $data['im_count'] = $this->format->integer($imdb->body->imdbVotes);
        } else {
            $data['im_count'] = null;
        }

		if (empty($imdb->body->Ratings)) {
			$data['rt_rat'] = null;
		} else {
			foreach ($imdb->body->Ratings as $ratings) {
				if ($ratings->Source == 'Rotten Tomatoes') $data['rt_rat'] = $this->format->integer($ratings->Value);
				else $data['rt_rat'] = null;
			}
		}
		
		$data['response'] = true;
		$data['message'] = 'Importamos datos de Imdb ok';
		return $data;
    }
}