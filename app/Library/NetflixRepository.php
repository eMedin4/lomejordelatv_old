<?php

namespace App\Library;

use App\Models\Netflix;
use App\Models\Movie;
use App\Models\ProvidersSeason;

class NetflixRepository 
{

    public function getItem($nfid)
    {
        return Netflix::where('netflix_id', $nfid)->with('movie')->first();
    }

    public function reset()
    {
        Netflix::where('online', 1)->update(['online' => 0]);
    }


    /*
        resetNetflixDates
        Pone todos los valores de las columnas new y expire de nuevo en null
    */
    public function resetDates()
    {
        return Netflix::where(true, true)->update(['new' => null, 'expire' => null]);
    }


    /*
        setNetflixDates
        Funcion: Actualiza la columna new o expire con la fecha dada e incrementa el valor de trend en 100
        Retorna: Numero de filas afectadas (normalmente será 0 o 1)
    */
    public function setDates($netflixId, $column, $date)
    {
        return Netflix::where('netflix_id', $netflixId)->increment('trend', 400, [$column => $date]);
    }

    public function existAndUpdate($netflixId)
    {
        $exist = Netflix::where('netflix_id', $netflixId)->exists();
        if ($exist) {
            Netflix::where('netflix_id', $netflixId)->update('online', 1);
        }
        return $exist;
    }


    /*
        searchItem
        Funcion: Busca Item desde datos de Netflix
        Retorna: modelo movie o false
    */
    public function searchItem($title, $year, $type)
    {
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
            'online' => 1,
        ]);
    }

    /*
        getItemsForSeasons
        Funcion: Busca y recibe items con get_seasons null o con fecha antigua para actualizar seasons
        Retorna: coleccion eloquent
    */
    public function getItemsForSeasons($dateLimit)
    {
        $nullItems = Netflix::where([['type', 'series'], ['online', 1], ['get_seasons_at', null]])->with('movie.seasonsTable')->take(90)->get();
        if ($nullItems->count() > 80) return $nullItems;

        $oldItems = Netflix::where([['type', 'series'], ['online', 1], ['get_seasons_at', '<', $dateLimit]])->with('movie.seasonsTable')->take(40)->get();
        $items = $nullItems->merge($oldItems);

        if ($items->count() > 40) $items->take(40);
        return $items;
    }

    public function updateGetSeasonsAt($id, $now)
    {
        Netflix::where('netflix_id', $id)->update(['get_seasons_at' => $now]);
    }


}


