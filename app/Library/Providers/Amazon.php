<?php
namespace App\Library\Providers;

use App\Library\Output;
use Illuminate\Support\Facades\Storage;

class Amazon
{

    private $output;

    public function __Construct(Output $output)
	{
        $this->output = $output;
    }
    
    public function run()
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

            //tenoirada
            if ($type == 'TV') {
                if ( substr( $row[4], 0, 9 ) === "Temporada" ) $data[$key]['season'] = $row[4];
                elseif ( substr( $row[5], 0, 9 ) === "Temporada" ) $data[$key]['season'] = $row[5];
                elseif ( substr( $row[6], 0, 9 ) === "Temporada" ) $data[$key]['season'] = $row[6];
                else $data[$key]['season'] = '';
            }
        }

        $matches = [];
        foreach($data as $key => $value) //key son los indices numericos y value son los subarrays
        {
            
            if ( $value['title'] === 'La que se avecina' )
                $matches[] = $value;
        }

        dd($matches);



        dd($data);
    }


    /*
        getMovieFromDb
        Funcion: Busca del api de netflix en nuestra base de datos
        Retorna: response = true, movie=modelo o response=false, reason=?
    */
    /*
    public function getMovieFromDb($title, $year, $type)
    {
        //Buscamos en baneadas
        $ban = $this->repository->checkBan($netflixid, 'nf');
        if ($ban) return ['response' => false, 'reason' => 'ban'];

        //Buscamos en verificadas
        $verify = $this->repository->checkVerify($netflixid, 'nf');
        if ($verify) {
            $movie = $this->repository->getMovieFromId($verify, 'fa');
            //si está en verificadas pero no existe en db la creamos
            if (is_null($movie)) {
                $setFromFaId = $this->createitems->runId('film' . $verify);
                if ($setFromFaId == false) return ['response' => false, 'reason' => 'importError', 'faid' => $verify];
                $movie = $this->repository->getMovieFromId($verify, 'fa');
            }
            return ['response' => true, 'movie' => $movie];
        }
        
        //Buscamos por el imid
        if (!empty($imdbid)) {
            $movie = $this->repository->getMovieFromId($imdbid, 'im');
            if ($movie) {
                $checkYears = $this->format->checkYears($movie->year, $released,2);
                if ($checkYears['response'] == false) {
                    $this->output->message("Netflix $netflixid : ($released) : Aceptada con reparos, Encontramos coincidencuia en imid pero no en año $movie->fa_id : ($movie->year)", true, 'error');
                }
                return ['response' => true, 'movie' => $movie];
            }
        }

        //Buscamos por título y año
        $movie = $this->repository->getMovieFromNetflix($this->format->decode($title), $released, $type);
        if ($movie) {
            return ['response' => true, 'movie' => $movie];
        }

        return ['response' => false, 'reason' => 'miss'];
    }

    */




}




