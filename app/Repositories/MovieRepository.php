<?php

namespace App\Repositories;

use App\Models\Character;
use App\Models\Genre;
use App\Models\Movie;
use App\Models\Param;
use App\Models\User;
use App\Models\MovistarTime;
use App\Models\Netflix;
use App\Models\Amazon;
use App\Models\Hbo;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MovieRepository {

    public function getMovie($slug)
    {
        return Movie::where('slug', $slug)->with(['movistarTime', 'movistarHistory'])->first();
    }

    public function getMovistar($type, $channel, $time, $sort)
    {

        $type = ($type == 'peliculas') ? 'movie' : 'show';
        $channelsTdt = ['TVE', 'LA2', 'C4', 'T5', 'A3', 'SEXTA'];
        $timeIntervals = $this->timeIntervals($time, $type);
        
        if ($sort == 'destacadas') $sort = 'trend';
        elseif ($sort == 'populares') $sort = 'fa_count';
        elseif ($sort == 'mejores') $sort = 'fa_rat';
        
        $records = MovistarTime::where('movistar_times.type', $type)
        ->join('movies', 'movistar_times.movie_id', '=', 'movies.id')
        ->whereBetween('time', $timeIntervals)
        ->orderBy($sort, 'desc')
        ->take(200)
        ->get();
        
        //Si usamos el metodo groupBy de colecciones te construye una estructura en la qu
        //que te muestra los repetidos, si lo metieramos en el query builder no se ven los repetidos
        $records = $records->groupBy('movie_id');

        /*//HELPER -> Muestra resultados formateados
        foreach ($records as $record) {
            echo $record->title . " rat: " . $record->fa_rat .  " count: " . $record->fa_count . " F/H: " . $record->time . " ID: " . $record->id . "<br>";
        }
        dd($newRecords, $records);*/
        
        return $records;
    }

    public function getNetflix($type, $list, $take = 50)
    {

        $type = ($type == 'peliculas') ? 'movie' : 'series';
        if ($list == 'recomendadas') $list = 'important';
        elseif ($list == 'trending') $list = 'trending';
        elseif ($list == 'mejores') $list = 'fa_rat';
        elseif ($list == 'populares') $list = 'fa_count';
        
        $records = Netflix::where([['type', $type], ['online', 1]])
        ->with(['movie', 'providersseasons' => function ($query) {
            $query->orderBy('number', 'asc');
        }])
        ->when($list == 'nuevas', function($q) {
            return $q->whereNotNull('new')->orderBy('new', 'desc');
        })
        ->when($list == 'expiran', function($q) {
            return $q->whereNotNull('expire')->orderBy('expire', 'desc');
        })
        ->when(($list == 'important') || ($list == 'trending'), function($q) use ($list, $take) {
            return $q->orderBy($list, 'desc')->take($take);
        })
        ->get();

        if (($list == 'fa_count') || ($list == 'fa_rat')) {
            $records = $records->sortByDesc('movie.' . $list)->take($take);
        }

        return $records;
    }

    public function getNetflixCount($type)
    {
        $type = ($type == 'peliculas') ? 'movie' : 'series';
        return Netflix::where('type', $type)->count();
    }

    public function getAmazon($type, $list, $take = 50)
    {
        $type = ($type == 'peliculas') ? 'movie' : 'TV';
        if ($list == 'recomendadas') $list = 'important';
        elseif ($list == 'trending') $list = 'trending';
        elseif ($list == 'mejores') $list = 'fa_rat';
        elseif ($list == 'populares') $list = 'fa_count';
        
        $records = Amazon::where([['type', $type], ['online', 1]])
        ->with(['movie', 'providersseasons' => function ($query) {
            $query->orderBy('number', 'asc');
        }])
        ->when(($list == 'important') || ($list == 'trending'), function($q) use ($list, $take) {
            return $q->orderBy($list, 'desc')->take($take);
        })
        ->get();

        if (($list == 'fa_count') || ($list == 'fa_rat')) {
            $records = $records->sortByDesc('movie.' . $list)->take($take);
        }

        return $records;
    }

    public function getAmazonCount($type)
    {
        $type = ($type == 'peliculas') ? 'movie' : 'TV';
        return Amazon::where('type', $type)->count();
    }


    public function getHbo($type, $list, $take = 50)
    {
        $type = ($type == 'peliculas') ? 'movie' : 'show';
        if ($list == 'recomendadas') $list = 'important';
        elseif ($list == 'trending') $list = 'trending';
        elseif ($list == 'mejores') $list = 'fa_rat';
        elseif ($list == 'populares') $list = 'fa_count';
        
        $records = Hbo::where([['type', $type], ['online', 1]])
        ->with(['movie', 'providersseasons' => function ($query) {
            $query->orderBy('number', 'asc');
        }])
        ->when(($list == 'important') || ($list == 'trending'), function($q) use ($list, $take) {
            return $q->orderBy($list, 'desc')->take($take);
        })
        ->get();

        if (($list == 'fa_count') || ($list == 'fa_rat')) {
            $records = $records->sortByDesc('movie.' . $list)->take($take);
        }

        return $records;
    }

    public function getHboCount($type)
    {
        $type = ($type == 'peliculas') ? 'movie' : 'show';
        return Hbo::where('type', $type)->count();
    }

    public function timeIntervals($time, $type)
    {
        $now = Carbon::now();
        $nowTime = $now->format('H:i:s');
        $timeBack = ($type == 'movie') ? 45 : 20; //Si es película cojemos las que llevan 45m empezadas, si es serie mucho menos

        if ($time == 'cualquier-momento')  return [Carbon::now()->subMinutes($timeBack), Carbon::now()->addDay(2)];

        if ($time == 'ahora') return [Carbon::now()->subMinutes($timeBack), Carbon::now()->addMinutes(30)];

        if ($time == 'hoy') return [Carbon::now()->subMinutes($timeBack), Carbon::now()->addDay()->setTime(1,0,0)];

        if ($time == 'esta-noche') {
            if ($nowTime > '20:45:00') return [Carbon::now()->subMinutes($timeBack), Carbon::now()->addDay()->setTime(1,0,0)];
            elseif ($nowTime >= '00:00:00' && $nowTime < '04:00:00') return [Carbon::now()->subMinutes($timeBack), Carbon::now()->setTime(6,0,0)];
            else return [Carbon::today()->setTime(20,0,0), Carbon::now()->addDay()->setTime(1,0,0)];
        }

        if ($time == 'manana') {
            if ($nowTime >= '00:00:00' && $nowTime < '05:00:00') return [Carbon::now()->subMinutes($timeBack), Carbon::now()->setTime(23,59,0)];
            else return [Carbon::now()->addDay()->setTime(3,0,0), Carbon::now()->addDay()->setTime(23,59,59)];
        }

    }

    public function liveSearch($string)
    {
        return Movie::search($string)->take(10)->get();
    }



}