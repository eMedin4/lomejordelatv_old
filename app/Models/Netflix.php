<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Netflix extends Model
{
    public $timestamps = false;
    public $table = 'netflix';

    public function movie()
	{
		return $this->belongsTo(Movie::class);
	}
}
