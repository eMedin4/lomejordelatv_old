<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProvidersSeason extends Model
{

    protected $fillable = ['number'];
    public $timestamps = false;

    public function provider()
    {
        return $this->morphTo();
    }
    


}
