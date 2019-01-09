<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hbo extends Model
{
    public $timestamps = false;
    public $table = 'hbo';

    public function movie()
	{
		return $this->belongsTo(Movie::class);
    }
    
    public function providersseasons()
    {
        return $this->morphMany(ProvidersSeason::class, 'provider');
    }


    public function getScoreAttribute()
	{
		return $this->movie->score;
    }

    public function getFaRatFormatAttribute()
    {
        return $this->movie->FaRatFormat;
    }
    

    public function getImRatFormatAttribute()
    {
        return $this->movie->ImRatFormat;
    }
    
}