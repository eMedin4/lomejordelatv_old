<?php

namespace App\Repositories;

use App\Models\Character;
use App\Models\Genre;
use App\Models\Movie;
use App\Models\Param;
use App\Models\User;
use App\Models\MovistarTime;
use App\Models\Netflix;
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
        $timeIntervals = $this->timeIntervals($time);
        
        if ($sort == 'destacadas') $sort = 'trend';
        elseif ($sort == 'populares') $sort = 'fa_count';
        elseif ($sort == 'mejores') $sort = 'fa_rat';
        
        $records = MovistarTime::where('movistar_times.type', $type)
        ->join('movies', 'movistar_times.movie_id', '=', 'movies.id')
        ->whereBetween('time', $timeIntervals)
        ->orderBy($sort, 'desc')
        ->take(50)
        ->get();

        /*foreach($records as $record) {
            echo "$record->title $record->fa_rat " . "<br>";
        }
        dd('fin');*/

        
        //Si usamos el metodo groupBy de colecciones te construye una estructura en la qu
        //que te muestra los repetidos, si lo metieramos en el query builder no se ven los repetidos
        $records = $records->groupBy('movie_id');

        /*//HELPER -> Muestra resultados formateados
        foreach ($records as $record) {
            echo $record->title . " rat: " . $record->fa_rat .  " count: " . $record->fa_count . " F/H: " . $record->time . " ID: " . $record->id . "<br>";
        }
        dd($newRecords, $records);*/

        //dd($records);
        
        return $records;
    }
    /* Antiguas consultas con eager loading, las hemos susbituido por joins
    ya que si no el orderBy dentro del with no funcionaba bien
    $records = MovistarTime::where('type', $type)
    ->whereBetween('time', $timeIntervals)
    ->with('movie')->groupBy('movie_id')->take(50)->get();
    $records = MovistarTime::where('type', $type)
    ->whereBetween('time', $timeIntervals)
    ->with(['movie' => function ($q) use ($sort) {
        $q->orderBy($sort, 'desc');
    }])->groupBy('movie_id')->take(50)->get(); */


    public function getNetflix($type, $query = null)
    {
        $selectDb = DB::table('netflix')->where('type', $type);

        if ($query = 'best') $selectDb->orderBy('trend', 'desc');
        if ($query = 'new') $selectDb->whereNotNull('new');

        $records = $selectDb->with('movie')->take(50)->get();
        return $records;
    }

    public function timeIntervals($time)
    {
        $now = Carbon::now();
        $nowTime = $now->format('H:i:s');

        if ($time == 'cualquier-momento')  return [Carbon::now()->subMinutes(60), Carbon::now()->addDay(2)];

        if ($time == 'ahora') return [Carbon::now()->subMinutes(60), Carbon::now()->addMinutes(30)];

        if ($time == 'hoy') return [Carbon::now()->subMinutes(45), Carbon::now()->addDay()->setTime(1,0,0)];

        if ($time == 'esta-noche') {
            if ($nowTime > '20:45:00') return [Carbon::now()->subMinutes(45), Carbon::now()->addDay()->setTime(1,0,0)];
            elseif ($nowTime >= '00:00:00' && $nowTime < '04:00:00') return [Carbon::now()->subMinutes(45), Carbon::now()->setTime(6,0,0)];
            else return [Carbon::today()->setTime(20,0,0), Carbon::now()->addDay()->setTime(1,0,0)];
        }

        if ($time == 'manana') {
            if ($nowTime >= '00:00:00' && $nowTime < '05:00:00') return [Carbon::now()->subMinutes(45), Carbon::now()->setTime(23,59,0)];
            else return [Carbon::now()->addDay()->setTime(3,0,0), Carbon::now()->addDay()->setTime(23,59,59)];
        }

    }



}