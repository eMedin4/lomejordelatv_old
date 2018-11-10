<?php
namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Administration\Format;
use Illuminate\Support\Facades\Log;

use Carbon\Carbon;

class Algorithm
{

	private $format;

    public function __Construct(Format $format)
	{
		$this->format = $format;
    }
	

    // COMPARA DATOS DE FILMAFFINITY CON TMDB PARA VERIFICAR ID TMDB
	public function checkTmdbId($faId, $faTitle, $faOriginal, $faYear, $tmdb, $deb)
	{
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

		//Modo debug
		if ($deb) {
			echo '<br><h3>' . 'Buscamos en TMDB: ' . '</h3>';
			echo '¿ExactMatch (coinciden titulo y original): <b>' . $exactMatchResponse . '</b>';
			if ($exactMatchResponse) {
				echo ' los Ids: ';
				foreach ($exactMatch as $item) {
					echo '<b>' . $item . ' </b>';
				}
			}
			echo '¿Y Match (coinciden combinaciones de titulo y original?: <b>' . $matchResponse . '</b>';
			if ($matchResponse) {
				echo ' los Ids: ';
				foreach ($match as $item) {
					echo '<b>' . $item . ' </b>';
				}
			}
			//dd($faTitle, $faOriginal, $exactMatchResponse, $exactMatch, $matchResponse, $match, $tmdb);
		}

		//RESPUESTAS
		if (($exactMatchResponse == True) && ($matchResponse == True) && ($exactMatchId == $matchId)) {
			return ['response' => True, 'id' => $exactMatchId];
		} elseif (($exactMatchResponse == True) && ($matchResponse == True) && ($exactMatchId != $matchId)) {
			Log::channel('customMovies')->debug('Rechazada, checkTmdbId: exactMatch ok, match ok, pero ids entre ellos no coinciden: ' . $faId);
			return ['response' => False];
		} elseif (($exactMatchResponse == True) && ($matchResponse == False)) {
			Log::channel('customMovies')->debug('Aceptada pero para revisar. checkTmdbId: exactMatch ok, match ko: ' . $faId);
			return ['response' => True, 'id' => $exactMatchId];
		} elseif (($exactMatchResponse == False) && ($matchResponse == True)) {
			return ['response' => True, 'id' => $matchId];
		} else {
			return ['response' => False];
		}
	}
	
	public function popularity($year, $count, $source)
	{

		//establecemos los máximos
		if ($source == 'fa') {
			$source_highest = 50000;
		} else if ($source == 'im') {
			$source_highest = 200000;
		} else {
			return 'error en algoritmo';
		}

		//step1 : convertimos el count en relativo a 10
		if ($count > $source_highest) $count = $source_highest;
		$step1 = $count/($source_highest/10);
		$step1 = round($step1, 1);
		
		//step2 : calculamos el coeficiente de año y multiplicamos
		$yearMax = 2019;
		$yearMin = 1999;
		$yearCoefMax = 1.5;
		$yearCoefMin = 0.5;
		if ($year < $yearMin || $year == null) $year = $yearMin;
		$yearCoef = ((($year - $yearMin) * ($yearCoefMax - $yearCoefMin)) / ($yearMax - $yearMin)) + $yearCoefMin;
		$yearCoef = round($yearCoef, 1);
		$step2 = round($step1 * $yearCoef, 1);
		if ($step2 > 10) $step2 = 10;

		//class: calculamos la clase para css, de 0 a 5
		$class = (int)($step2 / 2);
		
		return [
			"step1" => $step1, 
			"step2" => $step2, 
			"class" => $class
		];
		
	}

	
    

    
}
