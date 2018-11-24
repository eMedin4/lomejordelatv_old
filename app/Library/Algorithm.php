<?php
namespace App\Library;

use App\Library\Format;
use Illuminate\Support\Facades\Log;

use Carbon\Carbon;

class Algorithm
{

	private $format;

    public function __Construct(Format $format)
	{
		$this->format = $format;
    }
	
	public function popularity($year, $count, $source)
	{

		//establecemos los máximos
		if ($source == 'fa') {
			$source_highest = 50000;
		} else if ($source == 'im') {
			$source_highest = 100000;
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
		$yearCoefMax = 2.2;
		$yearCoefMin = 0.3;
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
