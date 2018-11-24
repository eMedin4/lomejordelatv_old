<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Verified extends Model
{
	public $timestamps = false;

	protected $fillable = ['source_1', 'source_2', 'id_1', 'id_2'];
}
