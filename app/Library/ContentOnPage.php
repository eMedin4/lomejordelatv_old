<?php
namespace App\Library;

use Illuminate\Support\Facades\View;

class ContentOnPage
{

    public function getPage($parameters)
    {
        $page = $parameters['type'] . '-' . $parameters['channel'] . '-' . $parameters['list']; 
        //dd($parameters, $page);
        return [
            'h1' => $this->getH1($page),
            'text' => $this->getText($page),
            'seo' => $this->getSeo($page),
        ];
    }

	public function getH1($page)
	{
        $values = [
            'peliculas-tv-cualquier-momento' => 'Películas TV',
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

            'peliculas-netflix-todas' => 'Películas Netflix',
            'series-netflix-todas' => 'Series Netflix',
        ];
        if (array_key_exists($page, $values)) return $values[$page];
        return str_replace('-', ' ', $page);
    }

    public function getText($page)
	{
        $values = [
            'peliculas-tv-cualquier-momento' => 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. 
            Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, 
            pretium quis, sem. Nulla consequat massa quis enim.',
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

            'peliculas-netflix-todas' => 'Las mejores peliculas de Netflix no siempre son las más fáciles de encontrar en la aplicación. Por eso en Lomejordelatv hemos creado un algorítmo alternativo e independiente, para ofrecerte clasificadas y ordenadas las películas más recomendadas. Además podrás filtrarlas como desees.',
            'peliculas-netflix-todas' => 'Netflix dispone de un gran motor de recomendación de títulos personalizado para cada usuario. Funciona realmente bien, lamentablemente no lo abarca todo y el resultado es que no verás muchas de las mejores series de Netflix.</p> <p>En Lomejordelatv te ofrecemos todo el catálogo para que lo filtres y ordenes como desees, además tenemos nuestro propio motor de recomendación basado en la popularidad en Netflix y en las calificaciones de Filmaffinity e Imdb.',
        ];
        if (array_key_exists($page, $values)) return $values[$page];
        return '';
    }

    public function getSeo($page)
	{
        $values = [
            'peliculas-tv-cualquier-momento' => '',
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