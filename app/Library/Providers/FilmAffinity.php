<?php
namespace App\Library\Providers;

use App\Library\Format;

class FilmAffinity
{
	private $format;

    public function __Construct(Format $format)
	{
		$this->format = $format;
	}

    public function getMovie($crawler, $minDuration = 0)
    {
		// Responde array con keys: response, revision(?), message(?), fa_title(?), fa_original(?),...

        //Scrapeamos película
		$faData['fa_id'] = $this->format->faId($crawler->filter('.ntabs a')->eq(0)->attr('href'));
		$faData['fa_title'] = $crawler->filter('#main-title span')->text();
		$faData['fa_type'] = (preg_match('/\((Serie de TV)\)|\((Miniserie de TV)\)/', $faData['fa_title'])) ? 'show' : 'movie';
		$faData['fa_title'] = $this->format->removeString($faData['fa_title'], ['(TV)', '(Serie de TV)', '(Miniserie de TV)']);

		//Construimos array con los datos de la table(no tienen ids)
        $table = $crawler->filter('.movie-info dt')->each(function($element) { return [$element->text() => $element->nextAll()->text()]; });
		
		//Devuelve un array de arrays, lo convertimos a array normal
        foreach ($table as $key => $value) $table2[key($value)] = current($value);

        //Datos de la tabla de fa
		$faData['fa_original'] = $this->format->removeString($this->format->cleanData($this->format->getValueIfExist($table2, 'Título original')), 'aka');
		$faData['fa_original'] = $this->format->removeString($faData['fa_original'], ['(TV)', '(TV Series)']);
		$faData['fa_year'] = $this->format->getValueIfExist($table2, 'Año');
		$faData['fa_duration'] = $this->format->integer($this->format->getValueIfExist($table2, 'Duración'));
		$faData['fa_country'] = $this->format->cleanData($this->format->getValueIfExist($table2, 'País'));
		$faData['fa_review'] = $this->format->removeString($this->format->getValueIfExist($table2, 'Sinopsis'), '(FILMAFFINITY)');
		$faData['fa_director'] = $this->format->cleanData($this->format->getValueIfExist($table2, 'Dirección'));

		//Rechazadas
		if (($faData['fa_duration'] != 0) && ($faData['fa_duration'] < $minDuration)) {
			$faData['response'] = false;
			$faData['revision'] = false;
			$faData['message'] = $faData['fa_title'] . ' : Rechazada: Duración inferior a 30 minutos';
			return $faData;
		}
		if ($faData['fa_year'] == null) {
			$faData['response'] = false;
			$faData['revision'] = true;
			$faData['message'] = $faData['fa_title'] . ' : Rechazada: No tiene año';
			return $faData;
		}

		// fa_rat y fa_count
		$faRat = $this->format->float($this->format->getElementIfExist($crawler, '#movie-rat-avg', NULL));
		$faCount = $this->format->getElementIfExist($crawler, '#movie-count-rat span', NULL);
		$faCount = $this->format->integer(str_replace('.', '', $faCount));
		if ($faRat && $faCount) {
			$faData['fa_rat'] = $faRat;
			$faData['fa_count'] = $faCount;
		} else {
			$faData['fa_rat'] = $faData['fa_count'] = NULL;
		}
		
		//image
		$faData['fa_image'] = $crawler->filter('#movie-main-image-container img')->attr('src');

        $faData['response'] = true;
        return $faData;
	}
	
	public function getCard($element, $style='standard')
	{
		// Responde array con keys: href, fa_id, fa_title, fa_rat y fa_count
		if ($style == 'standard') {
			$faData['href']  = $element->filter('.movie-card .mc-title a'); 
			$faData['fa_id'] = $this->format->faId($faData['href']->attr('href'));
			$faData['fa_title'] = $element->filter('.movie-card .mc-title a')->text(); 
			$faData['fa_rat'] = $this->format->score($element->filter('.avgrat-box')->text());
			$faData['fa_count'] = $this->format->integer($this->format->getElementIfExist($element, '.ratcount-box', 0));
		} else {
			$faData['href']  = $element->filter('h3 a');
			$faData['fa_id'] = $this->format->faId($faData['href']->attr('href'));
			$faData['fa_title'] = $element->filter('h3 a')->text(); 
			$faData['fa_rat'] = $this->format->score($element->filter('.avg-rating')->text());
			$faData['fa_count'] = $this->format->integer($this->format->getElementIfExist($element, '.ratcount-box', 0));
		}
		return $faData;
    }

}