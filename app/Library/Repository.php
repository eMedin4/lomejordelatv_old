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

Use App\Library\Images;
use Carbon\Carbon;

class Repository {

    private $images;

    public function __Construct(Images $images)
    {
        $this->images = $images;
    }

    public function checkIfExist($id)
    {
        $movie = Movie::where('fa_id', $id)->first();
        if ($movie) return true;
        return false;
    }

    public function getMovieFromFaId($faid)
    {
        $movie = Movie::where('fa_id', $faid)->first();
        if ($movie) return $movie;
        return false;
    }

    public function update($card)
    {
        $movie = Movie::where('fa_id', $card['fa_id'])->first();
        $movie->fa_rat = $card['fa_rat'];
        $movie->fa_count = $card['fa_count'];
        $movie->save();
    }

    public function storeMovie($data, $source)
    {
        $movie = Movie::firstOrNew(['fa_id' => $data['fa_id']]);

        if ($movie->exists) $status = 'updated';
        else $status = 'created';

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
        $movie->fa_popularity       = $data['fa_popularity']['step2'];
        $movie->im_popularity       = $data['im_popularity']['step2'];
        $movie->fa_popularity_class = $data['fa_popularity']['class'];
        $movie->im_popularity_class = $data['im_popularity']['class'];
        $movie->reliable_duration   = false;

        if (!$movie->exists) { /*solo recalculamos slugs para nuevas películas*/
            $movie->slug             = $this->setSlug($data['fa_title']);
        }

        $poster = isset($data['poster']) ? $data['poster'] : null;
        $background = isset($data['background']) ? $data['background'] : null;

        if ($poster) {
            $savePoster = $this->images->savePoster($poster, $movie->slug, $source);
            if ($savePoster) $movie->check_poster = 1;
            else $movie->check_poster = 0;
        }

        if ($background) {
            $saveBackground = $this->images->saveBackground($background, $movie->slug, $source);
            if ($saveBackground) $movie->check_background = 1;
            else $movie->check_background = 0;
        }

        //GUARDAMOS TODO
        $movie->save();

        //GUARDAR CARÁCTERES
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

        //SINCRONIZAMOS GENRES
        $values = array_column($data['genres'], 'id');
        $movie->genres()->sync($values);

        return ['status' => $status, 'id' => $movie->id];

    }

    public function setSlug($slug)
    {
        $slug = str_slug($slug, '-');
        $count = Movie::whereRaw("slug RLIKE '^{$slug}(-[0-9]+)?$'")->count();
        if (empty($count)) return $slug; //si no hay slug retornamos el slug normal

        //ya hay algún slug
        $check = $this->checkSlug($slug, $count);
        if (empty($check)) return "{$slug}-{$count}"; //verificamos nuestro slug y si no existe lo retornamos

        //si nuestro nuevo slug ya existe vamos a ir probando nuevos
        for ($i=1; $i < 10; $i++) { 
            $check = $this->checkSlug($slug, $i);
            if (empty($check)) return "{$slug}-{$i}";
        }
    }

    public function checkSlug($slug, $count)
    {
        return Movie::where('slug', "{$slug}-{$count}")->count();
    }

    //ACTUALIZA TODOS LOS GENEROS
    public function updateAllGenres($apiGenres)
    {
        foreach($apiGenres as $genre) {
            Genre::firstOrCreate(['id' => $genre->id], ['name' => $genre->name]);
        }
    }

    public function setParam($name, $value=NULL, $date=NULL) 
    {
        //SI EXISTE UNA FILA CON EL NOMBRE QUE VAMOS A GUARDAR, ANTES LA BORRAMOS
        $old = Param::where('name', $name);
        if ($old->count() > 0) {
            $old->delete();
        }

        $param = New Param;
        $param->name = $name;
        $param->value = $value;
        $param->date = $date;
        $param->save();
    }

    public function getParam($name, $column)
    {
        return Param::where('name', $name)->value($column);
    }

    public function resetMovistar()
    {
        if (MovistarTime::where('time', '<', Carbon::now()->subHours(5))->count()) {
            $items = MovistarTime::where('time', '<', Carbon::now()->subHours(5))->get(['time', 'channel', 'channel_code', 'movie_id']);
            MovistarHistory::insert($items->toArray());
            $items = MovistarTime::where('time', '<', Carbon::now()->subHours(5))->delete();
        }
    }

    public function resetNetflix()
    {
        Netflix::truncate();
    }

    public function setMovie($movie, $datetime, $channelCode, $channel)
    {
        $match = MovistarTime::where([['movie_id', '=', $movie->id],['time', '=', $datetime]])->first();
        if ($match) return;
        MovistarTime::insert(
            ['time' => $datetime, 'channel' => $channel, 'channel_code' => $channelCode, 'movie_id' => $movie->id]
        );
    }

    public function searchByExactTitle($title)
    {
        $movies = Movie::where('title', $title)->get();
        if ($movies->count() == 1) return $movies->first();
        else return false;
    }

    public function searchByExactSlug($title)
    {
        $slug = str_slug($title, '-');
        $movies = Movie::where('slug', 'like', '%' . $slug . '%')->get();
        if ($movies->count() == 1) return $movies->first();
        else return false;
    }

    public function searchFromMovistarByDetails($title, $original, $year, $duration)
    {

		$movies = Movie::where('title', 'like', '%' . $title . '%')
			->whereBetween('year', [$year - 1, $year + 1])
			->whereBetween('duration', [$duration - 5, $duration + 5])
			->get();

		if ($movies->count() == 1) return $movies->first();

		$movies = Movie::where('original_title', 'like', '%' . $original . '%')
			->whereBetween('year', [$year - 1, $year + 1])
			->whereBetween('duration', [$duration - 5, $duration + 5])
			->get();

		if ($movies->count() == 1) return $movies->first();

		return false;
    }
    
    public function getMovieFromNetflix($title, $year)
    {
        //devuelve id o false

        $movies = Movie::where('original_title', $title)
            ->whereBetween('year', [$year - 1, $year + 1])
            ->get();
   
        if ($movies->count() == 1) {  
            $movie = $movies->first();
            return $movies->first();
        }

        $movies = Movie::where('original_title', 'like', '%' . $title . '%')
            ->whereBetween('year', [$year - 1, $year + 1])
            ->get();

        if ($movies->count() == 1) {
            $movie = $movies->first();
            return $movies->first();
        }

        $movies = Movie::where('slug', 'like', '%' . str_slug($title) . '%')
            ->whereBetween('year', [$year - 1, $year + 1])
            ->get();

        if ($movies->count() == 1) {   
            $movie = $movies->first();
            return $movies->first();
        }
   
        return false;
    }

    public function setNetflix($nfid, $id)
    {
        Netflix::insert([
            'netflix_id' => $nfid,
            'movie_id'=> $id
        ]);
    }

    public function setNetflixLogs($nfOriginal, $nfYear, $nfImid, $dbOriginal, $dbYear, $dbImid)
    {
        NetflixLogs::insert([
            'db_original' => $dbOriginal,
            'db_year' => $dbYear,
            'db_imdb' => $dbImid,
            'nf_original' => $nfOriginal,
            'nf_year' => $nfYear,
            'nf_imdb' => $nfImid,
        ]);
    }

    /*
        checkVerify
        Funcion: Busca en Verifieds. Si pasas fa devuelve id_2, si pasas otro devuelve id_1 (de fa)
        Retorna: id o false
    */
    public function checkVerify($id, $source)
    {
        if ($source == 'fa') {
            $verify = Verified::where('id_1', $id)->first();
            if ($verify) return $verify->id_2;
            else return false;
        } else {
            $verify = Verified::where([['source_2', $source], ['id_2', $id]])->first();
            if ($verify) return $verify->id_1;
            else return false;
        }
    }

    /*
        getMovieFromId 
        Funcion: Busca en Movies por fa_id, tm_id o im_id. Si source es 'db' buscará por el id.
        Retorna: modelo Movie o null
    */
    public function getMovieFromId($id, $source)
    {
        if ($source == 'db') return Movie::find($id);
        $column = $source . '_id';
        return Movie::where($column, $id)->first();
    }


    public function setVerify($source1, $id1, $source2, $id2)
    {
        //retorna true si la crea ok y false si ya existía en db
        $verify = Verified::firstOrCreate(
            ['source_1' => 'fa', 'id_1' => $id1], 
            ['source_1' => $source1, 'source_2' => $source2, 'id_1' => $id1, 'id_2' => $id2]
        );
        return $verify->wasRecentlyCreated;
    }


}


