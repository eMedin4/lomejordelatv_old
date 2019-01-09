<?php

namespace App\Library;

use App\Models\Hbo;
use App\Models\Movie;
use App\Models\ProvidersSeason;

class HboRepository 
{

    public function searchItem($title, $type)
    {
        $movies = Movie::where([['title', $title], ['type', $type]])->get();
        if ($movies->count() == 1) return $movies->first();
        $movies = Movie::where([['original_title', $title], ['type', $type]])->get();
        if ($movies->count() == 1) return $movies->first();
        $movies = Movie::where([['title', 'like', '%' . $title . '%'], ['type', $type]])->get();
        if ($movies->count() == 1) return $movies->first();
        $movies = Movie::where([['slug', 'like', '%' . str_slug($title) . '%'], ['type', $type]])->get();
        if ($movies->count() == 1) return $movies->first();
        return false;
    }

    public function existAndUpdate($title)
    {
        $exist = Hbo::where('title', $title)->exists();
        if ($exist) {
            Hbo::where('title', $title)->update(['online' => 1]);
        }
        return $exist;
    }

    public function setHbo($title, $link, $id, $type, $popularity)
    {
        $modifier = rand(5,15) / 10;
        $hot = ($popularity > 1000) ? 1 : 0;
        $popularity = $popularity * $modifier;

        return Hbo::insertGetId([
            'title' => $title,
            'url' => $link,
            'movie_id'=> $id,
            'type' => $type,
            'hot' => $hot,
            'trend' => $popularity,
            'online' => 1,
        ]);
    }

    public function reset()
    {
        Hbo::where('online', 1)->update(['online' => 0]);
    }

}


