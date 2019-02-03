<?php
namespace App\Library\Providers;

use App\Library\Output;
use App\Library\Format;
use App\Library\HboRepository;
use App\Library\GenericRepository;
use App\Library\ItemCreation;

use Illuminate\Support\Facades\Storage;

class Hbo
{
    private $hboRepository;
    private $genericRepository;
    private $itemCreation;
    private $output;
    private $format;

    public function __Construct(HboRepository $hboRepository, GenericRepository $genericRepository, Output $output, Format $format, ItemCreation $itemCreation)
	{
        $this->output = $output;
        $this->hboRepository = $hboRepository;
        $this->genericRepository = $genericRepository;
        $this->format = $format;
        $this->itemCreation = $itemCreation;
    }
    
    public function run()
    {

        $this->hboRepository->reset();
        $data = $this->request();

        foreach($data as $item) {

            $movie = $this->searchItem($item);

            if ($movie) {

                //guardamos la pelicula
                $amazonDbId = $this->hboRepository->setHbo($item['title'], $item['link'], $movie->id, $item['type'], $movie->fa_popularity);
                $this->output->message("Guardada ok :: " . $item['title'] . " :: " . $movie->title, false);

            }
            
        }

        dd('terminado');
    }


    /*
        searchItem
        Funcion: Buscamos item de Amazon en tablas Amazon, baneadas, verificadas, y movies
        Retorna: false si la encuentra en Amazon, bans o verifys; $movie si la encuentra nueva en movies, si no la encuentra retorna false y escribe en el log
    */
    public function searchItem($item)
    {

        // Comprobamos y terminamos si existe
        $exist = $this->hboRepository->existAndUpdate($item['title']);
        if ($exist) {
            $this->output->message("Ya existe, pasamos a online=1" . $item['title'], false);
            return false;
        }

        // Buscando en baneadas
        $ban = $this->genericRepository->checkBan($item['title'], 'hb');
        if ($ban) {
            $this->output->message("Baneada" . $item['title'], false);
            return false;
        } 

        // Buscamos en verificadas
        $verify = $this->genericRepository->checkVerify($item['title'], 'hb');

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
        if ($item['title'] == 'Supermax') {

            dd($item);
        }
        $movie = $this->hboRepository->searchItem($item['title'], $item['type']);

        if ($movie) {
            $this->output->message("Encontrada :: " . $item['title'] . " :: " . $movie->title, false);
            return $movie;
        } else {
            $this->output->message("No encontrada :: " . $item['title'], false, 'error');
            return false;
        }

    }


    /*
        request
        Funcion: Lee del csv y retorna un array formateado con type, title, link, year y season
        Retorna: Array de peliculas
    */
    public function request()
    {
        $file = Storage::get('hbo.csv');

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
            $type = (preg_match('#/all-(.*?)/#', $row[1], $match)) ? $match[1] : '';
            $data[$key]['type'] = ($type == 'movies') ? 'movie' : 'show';

            //titulo
            $data[$key]['title'] = $row[2];

            //enlace
            $data[$key]['link'] = $row[3];


        }

        //dd(array_slice($data, 0, 50));

        return $data;
    }

}
