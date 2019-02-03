<?php

namespace App\Library;

use App\Models\Amazon;
use App\Models\Movie;
use App\Models\ProvidersSeason;

class AmazonRepository 
{

    public function searchMovie($title, $year)
    {
        $fromYear = $year - 2;
        $toYear = $year + 2;
        $movies = Movie::where('title', $title)->whereBetween('year', [$fromYear, $toYear])->get();
        if ($movies->count() == 1) return $movies->first();
        $movies = Movie::where('original_title', $title)->whereBetween('year', [$fromYear, $toYear])->get();
        if ($movies->count() == 1) return $movies->first();
        $movies = Movie::where('original_title', 'like', '%' . $title . '%')->whereBetween('year', [$fromYear, $toYear])->get();
        if ($movies->count() == 1) return $movies->first();
        $movies = Movie::where('slug', 'like', '%' . str_slug($title) . '%')->whereBetween('year', [$fromYear, $toYear])->get();
        if ($movies->count() == 1) return $movies->first();
        return false;
    }

    public function searchShow($title, $minYear, $maxYear)
    {
        $fromMinYear = $minYear - 2;
        $toMinYear = $minYear + 2;
        $fromMaxYear = $maxYear - 2;
        $toMaxYear = $maxYear + 2;
        $movies = Movie::where([['title', $title], ['type', 'show']])->whereBetween('year', [$fromMinYear, $toMinYear])->get();
        if ($movies->count() == 1) return $movies->first();
        $movies = Movie::where([['original_title', $title], ['type', 'show']])->whereBetween('year', [$fromMinYear, $toMinYear])->get();
        if ($movies->count() == 1) return $movies->first();
        $movies = Movie::where([['original_title', 'like', '%' . $title . '%'], ['type', 'show']])->whereBetween('year', [$fromMinYear, $toMinYear])->get();
        if ($movies->count() == 1) return $movies->first();
        $movies = Movie::where([['slug', 'like', '%' . str_slug($title) . '%'], ['type', 'show']])->whereBetween('year', [$fromMinYear, $toMinYear])->get();
        if ($movies->count() == 1) return $movies->first();
        return false;
    }


    public function existAndUpdate($title)
    {
        $exist = Amazon::where('title', $title)->exists();
        if ($exist) {
            Amazon::where('title', $title)->update(['online' => 1]);
        }
        return $exist;
    }

    public function setAmazon($title, $link, $id, $type, $popularity)
    {
        $modifier = rand(5,15) / 10;
        $popularity = $popularity * $modifier;

        return Amazon::insertGetId([
            'title' => $title,
            'url' => $link,
            'movie_id'=> $id,
            'type' => $type,
            'important' => $popularity,
            'online' => 1,
        ]);
    }

    public function reset()
    {
        Amazon::where('online', 1)->update(['online' => 0]);
    }

}


