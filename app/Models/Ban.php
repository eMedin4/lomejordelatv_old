<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ban extends Model
{
	public $timestamps = false;

	protected $fillable = ['id', 'source'];
}
