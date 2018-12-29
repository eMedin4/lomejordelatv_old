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

use Carbon\Carbon;

class Repository {

    public function checkIfMovieExist($faid)
    {
        $movie = Movie::where('fa_id', $faid)->first();
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

    public function searchByExactTitle($title, $type)
    {
        $movies = Movie::where([['title', $title], ['type', $type]])->get();
        if ($movies->count() == 1) return $movies->first();
        else return false;
    }

    public function searchByExactSlug($title, $type)
    {
        $slug = str_slug($title, '-');
        $movies = Movie::where([['slug', 'like', '%' . $slug . '%'], ['type', $type]])->get();
        if ($movies->count() == 1) return $movies->first();
        else return false;
    }

    public function searchFromMovistarByDetails($title, $original, $year, $duration)
    {
        $movies = Movie::where('title', $title)
			->whereBetween('year', [$year - 1, $year + 1])
            ->get();          

		if ($movies->count() == 1) return $movies->first();

		$movies = Movie::where('title', 'like', '%' . $title . '%')
			->whereBetween('year', [$year - 1, $year + 1])
            ->get();          

		if ($movies->count() == 1) return $movies->first();

		$movies = Movie::where('original_title', 'like', '%' . $original . '%')
			->whereBetween('year', [$year - 1, $year + 1])
			->get();

		if ($movies->count() == 1) return $movies->first();

		return false;
    }
    
    public function getMovieFromNetflix($title, $year, $type)
    {
        //devuelve id o false
        if ($title == 'series') {

            $movies = Movie::where('original_title', $title)->get();
            if ($movies->count() == 1) return $movies->first();

            $movies = Movie::where('original_title', 'like', '%' . $title . '%')->get();
            if ($movies->count() == 1) return $movies->first();

            $movies = Movie::where('slug', 'like', '%' . str_slug($title) . '%')->get();
            if ($movies->count() == 1) return $movies->first();


        } else {

            $movies = Movie::where('original_title', $title)->whereBetween('year', [$year - 1, $year + 1])->get();
            if ($movies->count() == 1) return $movies->first();

            $movies = Movie::where('original_title', 'like', '%' . $title . '%')->whereBetween('year', [$year - 1, $year + 1])->get();
            if ($movies->count() == 1) return $movies->first();

            $movies = Movie::where('slug', 'like', '%' . str_slug($title) . '%')->whereBetween('year', [$year - 1, $year + 1])->get();
            if ($movies->count() == 1) return $movies->first();

        }
   
        return false;
    }


    public function setMovistar($id, $popularity, $datetime, $channelCode, $channel, $type, $season = null, $episode = null)
    {
        $match = MovistarTime::where([['movie_id', '=', $id],['time', '=', $datetime]])->first();
        if ($match) return;

        $modifier = rand(5,15) / 10;
        $hot = ($popularity > 1000) ? 1 : 0;
        $popularity = $popularity * $modifier;

        MovistarTime::insert([
            'time' => $datetime, 
            'channel' => $channel, 
            'channel_code' => $channelCode, 
            'movie_id' => $id, 
            'type' => $type, 
            'season' => $season, 
            'episode' => $episode,
            'hot' => $hot,
            'trend' => $popularity,
        ]);
    }


    public function setNetflix($nfid, $id, $type, $popularity)
    {
        $modifier = rand(5,15) / 10;
        $hot = ($popularity > 1000) ? 1 : 0;
        $popularity = $popularity * $modifier;

        Netflix::insert([
            'netflix_id' => $nfid,
            'movie_id'=> $id,
            'type' => $type,
            'hot' => $hot,
            'trend' => $popularity,
        ]);
    }

    /*
        setNetflixDates
        Funcion: Actualiza la columna new o expire con la fecha dada e incrementa el valor de trend en 100
        Retorna: Numero de filas afectadas (normalmente será 0 o 1)
    */
    public function setNetflixDates($netflixId, $column, $date)
    {
        return Netflix::where('netflix_id', $netflixId)->increment('trend', 400, [$column => $date]);
    }

    /*
        resetNetflixDates
        Pone todos los valores de las columnas new y expire de nuevo en null
    */
    public function resetNetflixDates()
    {
        return Netflix::where('new', null)->orWhere('expire', null)->update(['new' => NULL, 'expire' => NULL]);
    }

    /*CUARENTENA public function setNetflixLogs($nfOriginal, $nfYear, $nfImid, $dbOriginal, $dbYear, $dbImid)
    {
        NetflixLogs::insert([
            'db_original' => $dbOriginal,
            'db_year' => $dbYear,
            'db_imdb' => $dbImid,
            'nf_original' => $nfOriginal,
            'nf_year' => $nfYear,
            'nf_imdb' => $nfImid,
        ]);
    }*/

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
        checkBan
        Funcion: Busca baneadas. Si coincide id y source devuelve true, si no false
        Retorna: id o false
    */
    public function checkBan($id, $source)
    {
        $ban = Ban::where([['id_1', $id], ['source_1', $source]])->first();
        if ($ban) return true;
        else return false;
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
            ['source_1' => 'fa', 'source_2' => $source2, 'id_1' => $id1], 
            ['source_1' => 'fa', 'source_2' => $source2, 'id_1' => $id1, 'id_2' => $id2]
        );
        return $verify->wasRecentlyCreated;
    }


}


