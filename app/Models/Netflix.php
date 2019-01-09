<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Netflix extends Model
{
    public $timestamps = false;
    protected $dates = ['get_seasons_at'];
    public $table = 'netflix';

    public function movie()
	{
		return $this->belongsTo(Movie::class);
    }
    
    public function providersseasons()
    {
        return $this->morphMany(ProvidersSeason::class, 'provider');
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
