<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Season extends Model
{

    public $timestamps = false;
    
    public function movie()
	{
		return $this->belongsTo(Movie::class);
	}


}
