<?php
namespace App\Library;

use Illuminate\Support\Facades\View;

class ContentOnPage
{

    public function getPage($parameters)
    {
        $page = $parameters['type'] . '-' . $parameters['channel'] . '-' . $parameters['time']; 
        return [
            'h1' => $this->getH1($page),
            'text' => $this->getText($page),
            'seo' => $this->getSeo($page),
        ];
    }

	public function getH1($page)
	{
        $values = [
            'peliculas-tv-todas' => 'Películas TV',
            'series-tv-todas' => 'Series TV',
            'peliculas-tdt-todas' => 'Películas TV recomendadas',
            'series-tdt-todas' => 'Series TV recomendadas',
            'peliculas-tv-ahora' => 'Películas TV Ahora',
            'series-tv-ahora' => 'Series TV Ahora',
            'peliculas-tv-hoy' => 'Películas TV Hoy',
            'series-tv-hoy' => 'Series TV Hoy',
            'peliculas-tv-esta-noche' => 'Películas TV esta noche',
            'series-tv-esta-noche' => 'Series TV esta noche',
            'peliculas-tv-manana' => 'Películas TV Mañana',
            'series-tv-manana' => 'Series TV Mañana',
        ];
        if (array_key_exists($page, $values)) return $values[$page];
        return str_replace('-', ' ', $page);
    }

    public function getText($page)
	{
        $values = [
            'peliculas-tv-todas' => 'Todas las películas que emiten en televisión, ordenadas y clasificadas según su puntuación en IMDB y Filmaffinity.',
            'series-tv-todas' => '',
            'peliculas-tdt-todas' => '',
            'series-tdt-todas' => '',
            'peliculas-tv-ahora' => '',
            'series-tv-ahora' => '',
            'peliculas-tv-hoy' => '',
            'series-tv-hoy' => '',
            'peliculas-tv-esta-noche' => '',
            'series-tv-esta-noche' => '',
            'peliculas-tv-manana' => '',
            'series-tv-manana' => '',
        ];
        if (array_key_exists($page, $values)) return $values[$page];
        return '';
    }

    public function getSeo($page)
	{
        $values = [
            'peliculas-tv-todas' => '',
            'series-tv-todas' => '',
            'peliculas-tdt-todas' => '',
            'series-tdt-todas' => '',
            'peliculas-tv-ahora' => '',
            'series-tv-ahora' => '',
            'peliculas-tv-hoy' => '',
            'series-tv-hoy' => '',
            'peliculas-tv-esta-noche' => '',
            'series-tv-esta-noche' => '',
            'peliculas-tv-manana' => '',
            'series-tv-manana' => '',
        ];
        if (array_key_exists($page, $values)) return $values[$page];
        return '';
    }

}