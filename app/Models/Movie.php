<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Movie extends Model
{

	use Searchable;

	protected $fillable = ['fa_id'];
    
	public function genres()
	{
		return $this->belongsToMany(Genre::class);
	}

	public function characters()
    {
    	return $this->belongsToMany(Character::class)->withPivot('order');
	}

	public function directors()
	{
		return $this->characters()->where('department', 'director');
	}

	public function actors()
	{
		return $this->characters()->where('department', 'actor');
	}

	public function seasonsTable()
	{
		return $this->hasMany(Season::class);
	}

	public function movistarTime()
	{
		return $this->hasMany(MovistarTime::class);
	}

	public function movistarHistory()
	{
		return $this->hasMany(MovistarHistory::class);
	}

	public function Netflix()
	{
		return $this->hasOne(Netflix::class);
	}

	public function Amazon()
	{
		return $this->hasOne(Amazon::class);
	}

	public function Hbo()
	{
		return $this->hasOne(Hbo::class);
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

	public function getFormatFaCountAttribute()
	{
		return $this->formatRound($this->fa_count);
	}

	public function getFormatImCountAttribute()
	{
		return $this->formatRound($this->im_count);
	}

	public function formatRound($value)
	{
		if ($value < 1000) return '<1<small>K</small>' ;
		elseif ($value < 1000000) return floor($value / 1000) . '<small>K</small>';
		else return floor($value / 1000000) . '<small>M</small>';
	}


}
