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

    public function getNetflix($type, $time, $sort, $fromYear, $toYear)
    {

        $type = ($type == 'peliculas') ? 'movie' : 'series';
        
        if ($sort == 'destacadas') $sort = 'trend';
        elseif ($sort == 'populares') $sort = 'fa_count';
        elseif ($sort == 'mejores') $sort = 'fa_rat';
        
        $records = Netflix::where('netflix.type', $type)
        ->join('movies', 'netflix.movie_id', '=', 'movies.id')
        ->with('providersseasons')
        ->when($time == 'new', function($q, $time) {
            return $q->whereNotNull('new');
        })
        ->when($time == 'expire', function($q, $time) {
            return $q->whereNotNull('expire');
        })
        ->when($fromYear && $toYear, function($q) use($fromYear, $toYear) {
            return $q->whereBetween('year', [$fromYear, $toYear]);
        })
        ->orderBy($sort, 'desc')
        ->take(50)
        ->get();

        
        //Si usamos el metodo groupBy de colecciones te construye una estructura en la qu
        //que te muestra los repetidos, si lo metieramos en el query builder no se ven los repetidos
        $records = $records->groupBy('movie_id');
        return $records;
    }

    public function getAmazon($type, $sort)
    {
        $type = ($type == 'peliculas') ? 'movie' : 'TV';
        
        if ($sort == 'destacadas') $sort = 'trend';
        elseif ($sort == 'populares') $sort = 'fa_count';
        elseif ($sort == 'mejores') $sort = 'fa_rat';
        
        $records = Amazon::where('amazon.type', $type)
        ->join('movies', 'amazon.movie_id', '=', 'movies.id')
        ->with('providersseasons')
        ->orderBy($sort, 'desc')
        ->take(50)
        ->get();

        $records = $records->groupBy('movie_id');
        return $records;
    }

    public function getHbo($type, $sort)
    {
        $type = ($type == 'peliculas') ? 'movie' : 'TV';
        
        if ($sort == 'destacadas') $sort = 'trend';
        elseif ($sort == 'populares') $sort = 'fa_count';
        elseif ($sort == 'mejores') $sort = 'fa_rat';
        
        $records = Hbo::where('hbo.type', $type)
        ->join('movies', 'hbo.movie_id', '=', 'movies.id')
        ->with('providersseasons')
        ->orderBy($sort, 'desc')
        ->take(50)
        ->get();

        $records = $records->groupBy('movie_id');
        return $records;
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



}