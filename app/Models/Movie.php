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

	public function movistarHistory()
	{
		return $this->hasMany(MovistarHistory::class);
	}

	public function movistarTime()
	{
		return $this->hasMany(MovistarTime::class);
	}
	
	/* 
		ACCESSORS
	*/

	public function getFaStarsAttribute()
	{
		return $this->stars($this->fa_rat);
	}

	public function getImStarsAttribute()
	{
		return $this->stars($this->im_rat);
	}

	public function getRtStarsAttribute()
	{
		return $this->stars($this->rt_rat / 10);
	}

	public function stars($value)
	{
		switch (true) {
			case ($value >= 8): return '
				<span class="icon-star-full star-5-color"></span>
				<span class="icon-star-full star-5-color"></span>
				<span class="icon-star-full star-5-color"></span>
				<span class="icon-star-full star-5-color"></span>
				<span class="icon-star-full star-5-color star-large"></span>';
			case ($value >= 6.5): return '
				<span class="icon-star-full star-4-color"></span>
				<span class="icon-star-full star-4-color"></span>
				<span class="icon-star-full star-4-color"></span>
				<span class="icon-star-full star-4-color star-large"></span>
				<span class="icon-star-full star-4-nocolor"></span>';
            case ($value >= 5): return '
				<span class="icon-star-full star-3-color"></span>
				<span class="icon-star-full star-3-color"></span>
				<span class="icon-star-full star-3-color"></span>
				<span class="icon-star-full star-3-nocolor"></span>
				<span class="icon-star-full star-3-nocolor"></span>';
            case ($value >= 4): return '
				<span class="icon-star-full star-2-color"></span>
				<span class="icon-star-full star-2-color"></span>
				<span class="icon-star-full star-2-nocolor"></span>
				<span class="icon-star-full star-2-nocolor"></span>
				<span class="icon-star-full star-2-nocolor"></span>';
            default: return '
				<span class="icon-star-full star-1-color"></span>
				<span class="icon-star-full star-1-nocolor"></span>
				<span class="icon-star-full star-1-nocolor"></span>
				<span class="icon-star-full star-1-nocolor"></span>
				<span class="icon-star-full star-1-nocolor"></span>';
        } 
	}

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

	public function getScoreAttribute()
	{
		if ($this->fa_rat) return (int)$this->fa_rat;
		else return false;
	}


}
