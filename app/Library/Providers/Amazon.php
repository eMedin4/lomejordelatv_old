<?php
namespace App\Library\Providers;

use App\Library\Output;
use App\Library\Format;
use App\Library\AmazonRepository;
use App\Library\GenericRepository;
use App\Library\ItemCreation;

use Illuminate\Support\Facades\Storage;

class Amazon
{
    private $amazonRepository;
    private $genericRepository;
    private $itemCreation;
    private $output;
    private $format;

    public function __Construct(AmazonRepository $amazonRepository, GenericRepository $genericRepository, Output $output, Format $format, ItemCreation $itemCreation)
	{
        $this->output = $output;
        $this->amazonRepository = $amazonRepository;
        $this->genericRepository = $genericRepository;
        $this->format = $format;
        $this->itemCreation = $itemCreation;
    }
    
    public function run()
    {

        $this->amazonRepository->reset();
        $data = $this->request();

        foreach($data as $item) {

            $movie = $this->searchItem($item);

            if ($movie) {

                //guardamos la pelicula
                $amazonDbId = $this->amazonRepository->setAmazon($item['title'], $item['link'], $movie->id, $item['type'], $movie->fa_popularity);
                $this->output->message("Guardada ok :: " . $item['title'] . " :: " . $movie->title, false);

                if ($item['type'] == 'TV') {
                    $this->setSeasons($item['seasons'], $movie, $amazonDbId);
                    
                }
            }
            
        }

        dd('terminado');
    }

    public function setSeasons($seasons, $movie, $amazonDbId)
    {
        if($movie->seasonsTable()->exists()) { //Si existe true si no false
            $last = $movie->seasonsTable->max('number'); // numero con el último episodio
        } else {

            //si no tiene nada en la tabla seasons vamos a intentar descargarlo de tmdb
            $last = $this->itemCreation->runSeasons($movie->id, $movie->tm_id);
        }   

        //Si no encontramos seasons provisionalmente no descargamos de amazon, para revisar
        if ($last) {
            $this->genericRepository->setProvidersSeasons('am', $amazonDbId, $seasons, $last);
        }
    }


    /*
        searchItem
        Funcion: Buscamos item de Amazon en tablas Amazon, baneadas, verificadas, y movies
        Retorna: false si la encuentra en Amazon, bans o verifys; $movie si la encuentra nueva en movies, si no la encuentra retorna false y escribe en el log
    */
    public function searchItem($item)
    {

        // Comprobamos y terminamos si existe
        $exist = $this->amazonRepository->existAndUpdate($item['title']);
        if ($exist) {
            $this->output->message("Ya existe, pasamos a online=1" . $item['title'], false);
            return false;
        }

        // Buscando en baneadas
        $ban = $this->genericRepository->checkBan($item['title'], 'am');
        if ($ban) {
            $this->output->message("Baneada" . $item['title'], false);
            return false;
        } 

        // Buscamos en verificadas
        $verify = $this->genericRepository->checkVerify($item['title'], 'am');

        // Si existe en verificadas -> Buscamos el modelo movie
        if ($verify) {
            $movie = $this->genericRepository->getMovieFromId($verify, 'fa');
            
            // Si no existe el modelo lo creamos
            if (!$movie) {
                $itemCreation = $this->itemCreation->runId('film' . $verify);

                // Si da error al crear el Item
                if (!$itemCreation) {
                    $this->output->message("Error al crear en ItemCreation fa: $verify", true, 'error');
                    return false;
                }
            }

            // Devolvemos el modelo procedente de verificadas
            return $movie;
        }

        // Buscamos en db
        if ($item['type'] == 'Movie') {

            $movie = $this->amazonRepository->searchMovie($item['title'], $item['year']);
            if ($movie) {
                $this->output->message("Encontrada :: " . $item['title'] . ", " . $item['year'] . " :: $movie->title , $movie->year", false);
                return $movie;
            } else {
                $this->output->message("No encontrada :: " . $item['title'] . ", " . $item['year'], false, 'error');
                return false;
            }

        } else {

            $movie = $this->amazonRepository->searchShow($item['title'], $item['min_year'], $item['max_year']);
            if ($movie) {
                return $movie;
            } else {
                $this->output->message("No encontrada :: " . $item['title'] . ", " . $item['min_year'], false);
                return false;
            }
        }
    }


    /*
        request
        Funcion: Lee del csv y retorna un array formateado con type, title, link, year y season
        Retorna: Array de peliculas
    */
    public function request()
    {
        $file = Storage::get('primevideo.csv');

        //limpiamos el fichero
        $file = str_replace(array("\r", "\t"), '', $file);

        //convertimos en array
        $file = explode("\n", $file);

        //eliminamos el primer elemento (headers) y los vacíos
        array_shift($file);
        $file = array_filter($file); 

        $data = [];
        foreach ($file as $key => $row) {

            //Dividimos cada row por "," y previamente quitamos " del principo y final (sobran)
            $row = explode('","', trim($row,'"'));

            //tipo
            $type = (preg_match('/entity_type:%27(.*?)%/', $row[1], $match)) ? $match[1] : '';
            $data[$key]['type'] = $type;

            //titulo
            $data[$key]['title'] = $row[2];

            //enlace
            $data[$key]['link'] = $row[3];

            //año (puede estar en la posición 4, 5 o 6)
            if ( (strlen($row[4])) == 4 && ((strpos($row[4], "20") == 0) || (strpos($row[4], "19") == 0)) ) $data[$key]['year'] = $row[4];
            elseif ( (strlen($row[5])) == 4 && ((strpos($row[5], "20") == 0) || (strpos($row[5], "19") == 0)) ) $data[$key]['year'] = $row[5];
            elseif ( (strlen($row[6])) == 4 && ((strpos($row[6], "20") == 0) || (strpos($row[6], "19") == 0)) ) $data[$key]['year'] = $row[6];
            else $data[$key]['year'] = '';

            //temporada
            if ($type == 'TV') {
                if ( substr( $row[4], 0, 9 ) === "Temporada" ) $data[$key]['season'] = $this->format->toNumbers($row[4]);
                elseif ( substr( $row[5], 0, 9 ) === "Temporada" ) $data[$key]['season'] = $this->format->toNumbers($row[5]);
                elseif ( substr( $row[6], 0, 9 ) === "Temporada" ) $data[$key]['season'] = $this->format->toNumbers($row[6]);
                else $data[$key]['season'] = '';

                //a veces amazon nombra las temporadas por 101, 102, 103 o 1, 2, 3, 401, lo filtramos lo mejor que podemos
                if ($data[$key]['season'] > 100) {
                    if ($data[$key]['season'] < 110) {
                        $data[$key]['season'] = substr( $data[$key]['season'], -1);
                    } else {
                        $data[$key]['season'] = substr( $data[$key]['season'], 0, 1);
                    }
                }
            }
        }
        
        //Volvemos a recorrer para unificar items
        $result = [];
        foreach ($data as $item) {

            //creamos un solo array para cada item (si se repite no lo creamos)
            $title = $item['type'] . ' :: ' . $item['title'];
            if (!isset($result[$title])) $result[$title] = [];

            //rellenamos el array con los datos en funcion de si es una serie o una pelicula
            if ($item['type'] == 'TV') {
                $result[$title]['title'] = $item['title'];
                $result[$title]['type'] = $item['type'];
                $result[$title]['link'] = $item['link'];
                $result[$title]['max_year'] = (isset($result[$title]['max_year']) && ($result[$title]['max_year'] > $item['year'])) ? $result[$title]['max_year'] : $item['year'];
                $result[$title]['min_year'] = (isset($result[$title]['min_year']) && ($result[$title]['min_year'] < $item['year'])) ? $result[$title]['min_year'] : $item['year'];
                
                $numberOfSeason = $item['season'];
                $result[$title]['seasons'][$numberOfSeason] = $numberOfSeason;
                //$result[$title]['seasons'][] = $item;
            } else {
                $result[$title] = $item;
            }
        }

        
        //Ahora que ya no los necesitamos convertimos las keys del array a numericos
        $result = array_values($result);
        
        //dd(array_slice($result, 0, 10));
        //return array_slice($result, 0, 10);
        return $result;
    }

}
