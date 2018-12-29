<?php

namespace App\Library;

use App\Models\Character;
use App\Models\Comment;
use App\Models\Genre;
use App\Models\Movie;
use App\Models\Param;
use App\Models\User;
use App\Models\Netflix;
use App\Models\MovistarTime;
use App\Models\MovistarHistory;
use App\Models\NetflixLogs;
use App\Models\Verified;
use App\Models\Ban;
use App\Models\Season;

Use App\Library\Images;
use Carbon\Carbon;

class ItemRepository {

    private $images;

    public function __Construct(Images $images)
    {
        $this->images = $images;
    }


    /*
        run
        Funcion: Guarda todos los datos en las diferentes tablas para un item
        Retorna: status, id
    */
    public function run($data)
    {
        $movie = Movie::firstOrNew(['fa_id' => $data['fa_id']]);
        $status = $this->setStatus($movie->exists);

        //Guardamos datos principales
        $this->processMainData($movie, $data);

        //Si es nueva película recalculamos slug
        if ($status == 'created') $movie->slug = $this->setSlug($data['fa_title']);

        //Si es serie guardamos datos adicionales
        if ($data['fa_type'] == 'show') $this->processExtraShowData($movie, $data);

        //Procesamos todo lo relacionado con las imágenes
        $this->processImages($movie, $data);

        //Guardamos en la tabla movies
        $movie->save();

        //Guardamos en las tablas credits y pivote si existen
        if (array_key_exists('credits', $data)) $this->processCredits($movie, $data);

        //Guardamos en la tabla pivote genres
        $this->processGenres($movie, $data);

        return ['status' => $status, 'id' => $movie->id];
    }


    /*
        setStatus
        Funcion: Indica si firstOrNew devuelve true es que ya existia, si devuelve false es que la crea nueva
        Retorna: updated o created
    */
    public function setStatus($exists)
    {
        if ($exists) return 'updated';
        else return 'created';
    }


    /*
        processMainData
        Funcion: Guardamos datos principales en db
        Retorna: -
    */
    public function processMainData($movie, $data)
    {
        $movie->title               = $data['fa_title'];
        $movie->original_title      = $data['fa_original'];
        $movie->country             = $data['fa_country'];
        $movie->duration            = $data['fa_duration'];
        $movie->review              = $data['tm_review'] ? $data['tm_review'] : $data['fa_review'];
        $movie->fa_id               = $data['fa_id'];
        $movie->tm_id               = $data['tm_id'];
        $movie->year                = $data['fa_year'];
        $movie->im_id               = $data['im_id'];
        $movie->fa_rat              = $data['fa_rat'];
        $movie->fa_count            = $data['fa_count'];
        $movie->im_rat              = $data['im_rat'];
        $movie->im_count            = $data['im_count'];
        $movie->rt_rat              = $data['rt_rat'];
        $movie->popularity          = $data['popularity'];
        $movie->type                = $data['fa_type'];
    }


    /*
        processExtraShowData
        Funcion: Guardamos datos adicionales de series en db
        Retorna: -
    */
    public function processExtraShowData($movie, $data)
    {
        $movie->last_year = $data['tm_last_year'];
        $movie->seasons = $data['tm_number_of_seasons'];

        //guardamos en tabla season
        $this->processSeasons($movie->id, $movie->seasons);
    }


    /*
        processSeasons
        Funcion: Guarda en la tabla seasons
        Retorna: -
    */
    public function processSeasons($id, $seasons)
    {
        Season::where('movie_id', $id)->delete();
        $seasonsArray = [];
        foreach($seasons as $key => $season) {
            $seasonsArray[$key]['movie_id'] = $id;
            $seasonsArray[$key]['number'] = $season->season_number;
            $seasonsArray[$key]['year'] = substr($season->air_date, 0, 4);
            $seasonsArray[$key]['episodes'] = $season->episode_count;
            $seasonsArray[$key]['name'] = $season->name;
        }
        Season::insert($seasonsArray);
    }


    /*
        setSlug
        Funcion: Convertimos titulo a slug. Para ello tenemos que buscar slugs previos y si los hay añadir 1 al indice
        Retorna: -
    */
    public function setSlug($slug)
    {
        $slug = str_slug($slug, '-');
        $count = Movie::whereRaw("slug RLIKE '^{$slug}(-[0-9]+)?$'")->count();
        if (empty($count)) return $slug; //si no hay slug retornamos el slug normal

        //ya hay algún slug
        $check = Movie::where('slug', "{$slug}-{$count}")->count();
        if (empty($check)) return "{$slug}-{$count}"; //verificamos nuestro slug y si no existe lo retornamos

        //si nuestro nuevo slug ya existe vamos a ir probando nuevos
        for ($i=1; $i < 10; $i++) { 
            $check = Movie::where('slug', "{$slug}-{$count}")->count();
            if (empty($check)) return "{$slug}-{$i}";
        }
    }


    /*
        processImages
        Funcion: Guarda las imagenes y los campos de chequeo de imagenes
        Retorna: -
    */
    public function processImages($movie, $data)
    {
        $poster = isset($data['poster']) ? $data['poster'] : null;
        $background = isset($data['background']) ? $data['background'] : null;

        if ($poster) {
            $savePoster = $this->images->savePoster($poster, $movie->slug);
            if ($savePoster) $movie->check_poster = 1;
            else $movie->check_poster = 0;
        }

        if ($background) {
            $saveBackground = $this->images->saveBackground($background, $movie->slug);
            if ($saveBackground) $movie->check_background = 1;
            else $movie->check_background = 0;
        }
    }


    /*
        processCredits
        Funcion: Guarda credits en tabla characters y en tabla pivote
        Retorna: -
    */
    public function processCredits($movie, $data)
    {
        $movie->characters()->detach();

        foreach($data['credits']->cast as $i => $cast) {
            //GUARDAMOS ACTOR
            $character = Character::firstOrNew(['id' => $cast->id]);
            $character->id             = $cast->id;
            $character->name           = $cast->name;
            $character->department     = 'actor';
            $character->photo          = $cast->profile_path;
            $character->save();
            //GUARDAMOS EN ARRAY LISTO PARA SINCRONIZAR DESPUES
            $sync[$cast->id] = ['order' => $cast->order];
            //GUARDAMOS IMAGEN SI TIENE
            if ($cast->profile_path) {
                $this->images->saveCredit($cast->profile_path, $cast->name, $movie->id);
            }
        }

        foreach($data['credits']->crew as $i => $crew)
        {
            //SOLO GURADAMOS DIRECTOR
            if($crew->job == 'Director') {
                $character = Character::firstOrNew(['id' => $crew->id]);
                $character->id             = $crew->id;
                $character->name           = $crew->name;
                $character->department     = 'director';
                $character->photo          = $crew->profile_path;
                $character->save();
                //GUARDAMOS EN ARRAY LISTO PARA SINCRONIZAR DESPUES
                $sync[$crew->id] = ['order' => -1];
                //GUARDAMOS IMAGEN SI TIENE
                if ($crew->profile_path) {
                    $this->images->saveCredit($crew->profile_path, $crew->name, $movie->id);
                }
            }
        }

        //SINCRONIZAMOS TABLA PIVOTE DE CHARACTERS
        if (isset($sync)) {
            $movie->characters()->sync($sync);
        }
    }


    /*
        processGenres
        Funcion: Sincroniza en la tabla pivote genres
        Retorna: -
    */
    public function processGenres($movie, $data)
    {
        $values = array_column($data['genres'], 'id');
        $movie->genres()->sync($values);
    }


    /*
        updateAllGenres
        Funcion: Actualiza creditos de tmdb
        Retorna: -
    */
    public function updateTmdbGenres($genres)
    {
        foreach($genres as $genre) {
            Genre::firstOrCreate(['id' => $genre->id], ['name' => $genre->name]);
        }
    }
}


/* Data:
array:32 [
    "fa_id" => 801710
    "fa_title" => "Vikingos"
    "fa_type" => "show"
    "fa_original" => "Vikings"
    "fa_year" => "2013"
    "fa_duration" => 44
    "fa_country" => "Irlanda"
    "fa_review" => "Serie de TV (2013-Actualidad). Narra las aventuras del héroe Ragnar Lothbrok, de sus hermanos vikingos y su familia, cuando él se subleva para convertirse en el rey de las tribus vikingas. Además de ser un guerrero valiente, Ragnar encarna las tradiciones nórdicas de la devoción a los dioses. Según la leyenda era descendiente directo del dios Odín."
    "fa_director" => "Michael Hirst (Creator),                                                                            Ken Girotti,
                                                           Ciaran Donnelly,                                                                            Jeff Woolnough,                                                                            Stephen St. Leger,
                                  Helen Shaver,                                                                            Daniel Grou,
                                                            Johan Renck,                                                                            Kari Skogland,                                                                            Kelly Makin,
                        Sarah Harding,                                                                            Ben Bolt,
                                                David Wellington"
    "fa_rat" => 7.6
    "fa_count" => 27374
    "fa_image" => "https://pics.filmaffinity.com/vikings_tv_series-616055151-mmed.jpg"
    "response" => true
    "tm_id" => 44217
    "verified_manually" => false
    "message" => "Importamos datos de Imdb ok"
    "genres" => array:2 [
      0 => {#935
        +"id": 10759
        +"name": "Action & Adventure"
      }
      1 => {#981
        +"id": 18
        +"name": "Drama"
      }
    ]
    "im_id" => "tt2306299"
    "tm_review" => "Sigue las aventuras de Ragnar Lothbrok, el héroe más grande de su época. La serie narra las sagas de la banda de hermanos vikingos de Ragnar y su familia, cuando él se levanta para convertirse en el rey de las tribus vikingas. Además de ser un guerrero valiente, Ragnar encarna las tradiciones nórdicas de la devoción a los dioses, la leyenda dice que él era un descendiente directo de Odín, el dios de la guerra y los guerreros."
    "poster" => "/mBDlsOhNOV1MkNii81aT14EYQ4S.jpg"
    "background" => "/A30ZqEoDbchvE7mCZcSp6TEwB1Q.jpg"
    "tm_title" => "Vikingos"
    "tm_original" => "Vikings"
    "tm_year" => "2013"
    "tm_last_year" => "2018"
    "tm_countries" => array:1 [
      0 => "CA"
    ]
    "tm_seasons" => 5
    "tm_type" => "show"
    "im_rat" => 8.6
    "im_count" => 313536
    "rt_rat" => null
    "popularity" => 1916
  ]
  */