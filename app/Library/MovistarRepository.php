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
//use App\Models\Season;
use App\Models\ProvidersSeason;

use Carbon\Carbon;

class MovistarRepository 
{

    public function resetMovistar()
    {
        if (MovistarTime::where('time', '<', Carbon::now()->subHours(5))->count()) {
            $items = MovistarTime::where('time', '<', Carbon::now()->subHours(5))->get(['time', 'channel', 'channel_code', 'movie_id']);
            MovistarHistory::insert($items->toArray());
            $items = MovistarTime::where('time', '<', Carbon::now()->subHours(5))->delete();
        }
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


}
