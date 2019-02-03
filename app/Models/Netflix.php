<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Netflix extends Model
{
    public $timestamps = false;
    protected $dates = ['get_seasons_at', 'new', 'expire'];
    public $table = 'netflix';

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
