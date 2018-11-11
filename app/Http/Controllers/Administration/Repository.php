<?php

namespace App\Http\Controllers\Administration;

use App\Models\Character;
use App\Models\Comment;
use App\Models\Genre;
use App\Models\Movie;
use App\Models\Param;
use App\Models\User;
use App\Models\MovistarTime;
use App\Models\MovistarHistory;

Use App\Http\Controllers\Administration\Images;
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

    public function update($card)
    {
        $movie = Movie::where('fa_id', $card['fa_id'])->first();
        $movie->fa_rat = $card['fa_rat'];
        $movie->fa_count = $card['fa_count'];
        $movie->save();
    }

    public function storeMovie($data)
    {
        $movie = Movie::firstOrNew(['fa_id' => $data['fa_id']]);
        $movie->title            = $data['fa_title'];
        $movie->original_title   = $data['fa_original'];
        $movie->country          = $data['country'];
        $movie->duration         = $data['fa_duration'];
        $movie->review           = $data['tm_review'] ? $data['tm_review'] : $data['fa_review'];
        $movie->fa_id            = $data['fa_id'];
        $movie->tm_id            = $data['tm_id'];
        $movie->year             = $data['fa_year'];
        $movie->im_id            = $data['im_id'];
        $movie->fa_rat           = $data['fa_rat'];
        $movie->fa_count         = $data['fa_count'];
        $movie->im_rat           = $data['im_rat'];
        $movie->im_count         = $data['im_count'];
        $movie->rt_rat           = $data['rt_rat'];
        $movie->fa_popularity    = $data['fa_popularity']['step2'];
        $movie->im_popularity    = $data['im_popularity']['step2'];
        $movie->fa_popularity_class = $data['fa_popularity']['class'];
        $movie->im_popularity_class = $data['im_popularity']['class'];
        $movie->reliable_duration   = $data['reliable_duration'];

        if (!$movie->exists) { /*solo recalculamos slugs para nuevas películas*/
            $movie->slug             = $this->setSlug($data['fa_title']);
        }

        $poster = isset($data['poster']) ? $data['poster'] : null;
        $background = isset($data['background']) ? $data['poster'] : null;

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
    
    public function searchFromNetflix($original, $year, $duration)
    {
        $movies = Movie::where('original_title', $original)
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

}


