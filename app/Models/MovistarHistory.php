<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovistarHistory extends Model
{
    public $timestamps = false;
    public $table = 'movistar_history';
    protected $dates = ['time'];
}
