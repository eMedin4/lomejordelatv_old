<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{

	/* protected $guarded = []; */

	protected $fillable = ['fa_id'];
    
	public function genres()
	{
		return $this->belongsToMany(Genre::class);
	}

	public function characters()
    {
    	return $this->belongsToMany(Character::class)->withPivot('order');
	}

	public function seasonsTable()
	{
		return $this->hasMany(Season::class);
	}

	public function movistarTime()
	{
		return $this->hasMany(MovistarTime::class);
	}
	
	/* 
		ACCESSORS
	*/

	public function getExcerpt100Attribute()
	{
		return str_limit($this->review, 100, '...');
	}

	public function getExcerpt400Attribute()
	{
		return str_limit($this->review, 400, '...');
	}

	public function getExcerpt200Attribute()
	{
		return str_limit($this->review, 200, '...');
	}

	public function getFaRatFormatAttribute()
	{
		$faRat = explode('.', $this->fa_rat);
		return $faRat[0] . '<i>.' . $faRat[1] . '</i>';
	}

	public function getImRatFormatAttribute()
	{
		$imRat = explode('.', $this->im_rat);
		return $imRat[0] . '<i>.' . $imRat[1] . '</i>';
	}


}
