<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Amazon extends Model
{
    public $timestamps = false;
    public $table = 'amazon';

    public function movie()
	{
		return $this->belongsTo(Movie::class);
    }
    
    public function providersseasons()
    {
        return $this->morphMany(ProvidersSeason::class, 'provider');
    }

    // public function getFormatFaCountAttribute()
	// {
	// 	return $this->movie->formatFaCount;
	// }

	// public function getFormatImCountAttribute()
	// {
	// 	return $this->movie->formatImCount;
	// }
}
