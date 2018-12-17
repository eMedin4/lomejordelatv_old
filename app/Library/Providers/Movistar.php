<?php
namespace App\Library\Providers;

use Goutte\Client;
use Carbon\Carbon;
use App\Library\Repository;
use App\Library\Output;
use App\Models\MovistarLog;

class Movistar
{

    private $repository;
    private $output;

    public function __Construct(Repository $repository, Output $output)
	{
        $this->repository = $repository;
        $this->output = $output;
    }

    public function getMovies($source)
    {
        //borramos times ya pasados
        $this->repository->resetMovistar();
        $this->output->message('Borrada programación antigua', false, $source);

        $client = new Client();

        $daysToScrap = $this->daysToScrap();

        if (!$daysToScrap) {
            return;
        }

        foreach ($daysToScrap as $dayToScrap) {
            $this->output->message("Descargando fecha $dayToScrap", false, $source);
            foreach (config('movies.channels') as $channelCode => $channel) {
                $this->output->message("$channel ... ", false, $source, false);
                $url = 'http://www.movistarplus.es/guiamovil/' . $channelCode . '/' . $dayToScrap;
                $crawler = $client->request('GET', $url);
                if ($client->getResponse()->getStatus() !== 200) {
                    $this->output->message("$url devuelve error $client->getResponse()->getStatus()", true, $source);
                } 
                $this->scrapPage($client, $crawler, $dayToScrap, $channelCode, $channel);
                $this->output->message("ok", false, $source);
            }
            $this->output->message("Finalizada fecha $dayToScrap", true, $source);
            $this->repository->setParam('Movistar', Null, $dayToScrap);
        }
    }

    public function scrapPage($client, $crawler, $date, $channelCode, $channel)
    {
        //RECORREMOS FILAS DE CINE O SERIES
		$crawler->filter('.container_box.g_CN, .container_box.g_SR')->each(function($node, $i) use($client, $date, $channelCode, $channel) {
            
            $type = $node->filter('li.genre')->text();
            $title = trim($node->filter('li.title')->text());
            $time = $node->filter('li.time')->text();
            $datetime = $this->movistarDate($time, $date);
            $splitDay = $this->splitDay($date); //6 DE LA MAÑANA DEL DIA $DATE

            //SI LA HORA DE LA PELICULA ES ANTES DE LAS 6:00 (SPLITTIME) Y LA FILA DE LA TABLA ES DESPUES DE LA FILA 6, AÑADIMOS UN DÍA
			if ($datetime < $splitDay && $i > 6) {
				$datetime = $datetime->addDay();
            }
            
            //ANULAMOS SI EL TITULO COINCIDE CON FRASES BANEADAS
			foreach(config('movies.moviesTvBan') as $ban) {
				if (strpos($title, $ban) !== FALSE) {
                    MovistarLog::create(['movistar_title' => $title, 'datetime' => $datetime, 'channel' => $channel, 'valid' => 0, 'comment' => 'Baneada al encontrarse en la lista moviesTvBan']);
					return;
				}
            }
            
            //BORRAMOS PALABRAS BANEADAS DEL TITULO
            $title = str_replace(config('movies.wordsTvBan'), '', $title);

            //LIMPIAMOS TÍTULOS DE SERIES
            if ($type == 'Series') {
                //Principal sospechoso 1973 (T1): Episodio 5
                preg_match('#(T(.*?)):#', $title, $season);
                $title = trim(substr($title, 0, strrpos($title, "(T")));
            }

            dd($title, $season);

            dd($type, $title);
            
            //BUSCAMOS 1 COINCIDENCIA POR TITULO EXACTO
            $movie = $this->repository->searchByExactTitle($title);
            if ($movie) {
                MovistarLog::create(['movistar_title' => $title, 'fa_title' => $movie->title, 'fa_original' => $movie->original_title, 'datetime' => $datetime, 'channel' => $channel, 'valid' => 1, 'comment' => 'Encontrada sin entrar (por title único) ']);
                $this->repository->setMovie($movie, $datetime, $channelCode, $channel);
                return;
            } 

            //BUSCAMOS 1 COINCIDENCIA POR SLUG
            $movie = $this->repository->searchByExactSlug($title);
            if ($movie) {
                MovistarLog::create(['movistar_title' => $title, 'fa_title' => $movie->title, 'fa_original' => $movie->original_title, 'datetime' => $datetime, 'channel' => $channel, 'valid' => 1, 'comment' => 'Encontrada sin entrar (por slug único) ']);
                $this->repository->setMovie($movie, $datetime, $channelCode, $channel);
                return;
            } 

            //SI NO LA ENCONTRAMOS ENTRAMOS EN LA FICHA
			$page = $client->click($node->filter('a')->link());

            //ALGUNAS FICHAS DE 'CINE CUATRO', 'CINE BLOCKBUSTER',.. SIN PELICULA, NO TIENEN AÑO EN LA FICHA, ANULAMOS
            if ($page->filter('p[itemprop=datePublished]')->count() == 0) {
                MovistarLog::create(['movistar_title' => $title, 'datetime' => $datetime, 'channel' => $channel, 'valid' => 0, 'comment' => 'Baneada entrando, al no tener año dentro de la ficha de la película.']);
                return;
            }

            //ANULAMOS CUALQUIER PELICULA SIN DURACIÓN
            if ($page->filter('span[itemprop=duration]')->count() == 0) {
                MovistarLog::create(['movistar_title' => $title, 'datetime' => $datetime, 'channel' => $channel, 'valid' => 0, 'comment' => 'Baneada entrando, al no tener duración en la etiqueta itemprop']);
                return;
            }
            //ANULAMOS CUALQUIER PELÍCULA CON DURACIÓN DEMASIADO CORTA
            $duration = $page->filter('span[itemprop=duration]')->text();
            $duration = explode(':', $duration);
            $minutes = $duration[0] * 60 + (int)$duration[1];
            if ($minutes < 60) {
                MovistarLog::create(['movistar_title' => $title, 'datetime' => $datetime, 'channel' => $channel, 'valid' => 0, 'comment' => 'Baneada entrando, al tener una duración inferior a 1 hora']);
                return;
            }

            //COJEMOS DATOS
            $year = $page->filter('p[itemprop=datePublished]')->attr('content');
            $original = $this->getElementIfExist($page, '.title-especial p', NULL);

            //BUSCAMOS CON LOS DATOS
            $movie = $this->repository->searchFromMovistarByDetails($title, $original, $year, $minutes);

            if ($movie) {
                MovistarLog::create(['movistar_title' => $title, 'movistar_original' => $original, 'fa_title' => $movie->title, 'fa_original' => $movie->original_title, 'datetime' => $datetime, 'channel' => $channel, 'valid' => 1, 'comment' => 'Encontrada entrando, por detalles: ']);
                $this->repository->setMovie($movie, $datetime, $channelCode, $channel);
                return;
            }
            
            MovistarLog::create(['movistar_title' => $title, 'movistar_original' => $original, 'datetime' => $datetime, 'channel' => $channel, 'valid' => 0, 'comment' => 'No encontrada ni entrando']);

        });
    }

    public function daysToScrap()
    {
        //compara la fecha del último scraper con la actual y devuelve un array con las fechas que hay que scrapear
        $lastDayScraped = $this->repository->getParam('Movistar', 'date');
        $lastDayScraped = $lastDayScraped->format('Y-m-d');
        $today = Carbon::now()->toDateString();
        $tomorrow = Carbon::now()->addDay()->toDateString();
        if ($today > $lastDayScraped) {
            return [$today, $tomorrow];
        } elseif ($tomorrow > $lastDayScraped) {
            return [$tomorrow];
        } else {
            return;
        }
    }

    //$date = 'YYYY-MM-DD'; $time = 09:16;
    public function movistarDate($time, $date)
    {
    	$time = $this->cleanData($time);
    	$time = explode(':', $time);
    	$date = explode('-', $date);
		//año, mes, dia, hora, minuto, segundo, timezone
		return Carbon::create($date[0], $date[1], $date[2], $time[0], $time[1]);
    }

    public function splitDay($date)
    {
    	$date = explode('-', $date);
    	return Carbon::create($date[0], $date[1], $date[2], 6, 00);
    }

    //LIMPIAMOS EL STRING DE ESPACIOS, SALTOS DE LINEA,...
	public function cleanData($value)
	{
		$value = preg_replace('/\xA0/u', ' ', $value); //Elimina %C2%A0 del principio y resto de espacios
		$value = trim(str_replace(array("\r", "\n"), '', $value)); //elimina saltos de linea al principio y final
		return $value;
    }
    
    // DEVUELVE EL TEXTO SI EXISTE LA CLASE CSS O EL DEFAULT SI NO
	public function getElementIfExist($element, $class, $default) 
	{
		if ($element->filter($class)->count()) {
			return $element->filter($class)->text(); 
		} else {
			return $default;
		}	
    }

}