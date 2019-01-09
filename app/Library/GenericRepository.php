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
use App\Models\ProvidersSeason;

use Carbon\Carbon;

class GenericRepository {

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


    /*
        checkVerify
        Funcion: Busca en Verifieds. Si pasas fa devuelve id_2, si pasas otro devuelve id_1 (de fa)
        Retorna: id de coincidencia o false
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
    public function getMovieFromId($id, $source = 'db')
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

    public function setProvidersSeasons($provider, $providerId, $seasons, $last)
    {

        ProvidersSeason::where([['provider_type', $provider], ['provider_id', $providerId]])->delete();

        
        foreach ($seasons as $season) {

            //netflix incluye los capitulos en el array seasons ej. 0 => "1(10)", ...
            if ($provider == 'nf') $season = trim(preg_replace('/\s*\([^)]*\)/', '', $season));

            $isLast = ($season == $last) ? 1 : 0;

            ProvidersSeason::insert([
                'provider_type' => 'am',
                'provider_id' => $providerId,
                'number' => $season,
                'is_last' => $isLast,
            ]);
        }
    }
    

}


