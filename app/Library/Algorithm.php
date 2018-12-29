<?php
namespace App\Library;

use App\Library\Format;
use Illuminate\Support\Facades\Log;
use App\Models\Movie;

use Carbon\Carbon;

class Algorithm
{

	private $format;

    public function __Construct(Format $format)
	{
		$this->format = $format;
    }

	public function popularity($year, $endYear, $count, $type)
	{
		if (!$count) return 0;

		if ($endYear) $year = $endYear;
		
		$yearCoef = [
			2019 => 100,
			2018 => 70,
			2017 => 30,
			2016 => 12,
			2015 => 8,
			2014 => 5,
			2013 => 4,
			2012 => 3,
			2011 => 2,
			2010 => 1,
		];
		if ($year < 2010) $getYearCoef = 1;
		else $getYearCoef = $yearCoef[$year];
		
		$count = $count / 1000;
		$response = (int)($getYearCoef * $count);

		return $response;

		/*
			peliculas
			Origen 2010 150k
			roma 2018 4k
			okja 2017 11k
			mowgli 2018 2k

			series
			padre de familia 17t 1999-2018 100k
			walking dead 9t 2010-2018 71k
			shameless 9t 2011-2018 13k
			La maldicion de hill house 2018-2018 1t 8k
			Sons of anarchy 2008-2014 7t 21k
			Altered Carbon 2018-2018 1t 6k
			Desencanto 2018-2018 2t 4k
		*/
	}

	public function updatePopularity()
	{
		Movie::where('id', '>', '200')->chunk(100, function($movies) {
			foreach ($movies as $movie) {
				$newValue = $this->popularity($movie->year, $movie->last_year, $movie->fa_count, $movie->type);
				$movie->popularity = $newPopularity;
				$movie->save();
			}
		});
	}





	
}
