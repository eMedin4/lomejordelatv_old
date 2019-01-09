<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Carbon\Carbon;

class MovistarTime extends Model
{
	
	public $timestamps = false;
	protected $dates = ['time'];
	
	public $now, $today21, $tomorrow02, $tomorrow14, $tomorrow21, $aftertomorrow02;

	public function __Construct()
	{
		$this->now = Carbon::now();
		$this->today21 = Carbon::now()->setTime(21,0,0);
		if ($this->now > $this->today21) $this->today21 = $this->now->subHour();
		$this->tomorrow02 = Carbon::now()->addDay()->setTime(2,0,0);
		$this->tomorrow14 = Carbon::now()->addDay()->setTime(14,0,0);
		$this->tomorrow21 = Carbon::now()->addDay()->setTime(21,0,0);
		$this->aftertomorrow02 = Carbon::now()->addDay(2)->setTime(2,0,0);
	}
	
    public function movie()
	{
		return $this->belongsTo(Movie::class);
	}

	public function getFormatTimeAttribute()
    {
		$now = Carbon::now();
		$nowTime = $now->format('H:i:s');

		//En emisión
		if ($this->time < $now) return '<time><span class="time-alert">En emisión</span>' . $this->time->format('G:i') . '<span></span></time>';

		//Si ahora es la madrugada
		if ($nowTime >= '00:00:00' && $nowTime < '08:00:00') {
			if ($this->time->isToday()) {
				return '<time>' . $this->time->format('G:i') . ' <span>hoy ' . $this->time->formatLocalized('%a') . '</span></time>';
			} else {
				return '<time>' . $this->time->format('G:i') . ' <span>' . $this->time->formatLocalized('%a') . '</span></time>';
			}
		}

		//Si ahora es el dia 
		if ($nowTime >= '08:00:00' && $nowTime < '20:00:00') {
			if ($this->time->isToday()) {
				return '<time>' . $this->time->format('G:i') . ' <span>esta noche</span></time>';
			} else {
				return '<time>' . $this->time->format('G:i') . ' <span>' . $this->time->formatLocalized('%a') . '</span></time>';
			}
		}

		//Si ahora es la noche
		if ($nowTime >= '20:00:00' && $nowTime < '00:00:00') {
			//Si la pelicula empieza entre ahora  y las 02:00 de esta noche
			if ($this->time->between($now, Carbon::now()->addDay()->setTime(2,0,0))) {
				return '<time>' . $this->time->format('G:i') . ' <span>esta noche</span></time>';
			} else {
				return '<time>' . $this->time->format('G:i') . ' <span>' . $this->time->formatLocalized('%a') . '</span></time>';
			}
		}
	}

	public function getDayPartingAttribute()
	{
		switch (true) {
			case ($this->time->between($this->now->subHour(), $this->today21)): return ['help' => 'entre ahora y las 21', 'coeficient' => 0.8];
			case ($this->time->between($this->today21, $this->tomorrow02)): return ['help' => 'entre las 21 y las 02', 'coeficient' => 1];
			case ($this->time->between($this->tomorrow02, $this->tomorrow14)): return ['help' => 'entre las 02 y las 14 de mañana', 'coeficient' => 0.5];
			case ($this->time->between($this->tomorrow14, $this->tomorrow21)): return ['help' => 'entre las 14 y las 21 de mañana', 'coeficient' => 0.6];
			case ($this->time->between($this->tomorrow21, $this->aftertomorrow02)): return ['help' => 'entre las 21 y las 02 de mañana', 'coeficient' => 0.7];
            default: return ['help' => 'a partir de las 2 de pasado mañana', 'coeficient' => 0.4];
        } 
	}

	public function getExcerpt200Attribute()
	{
		return $this->movie->excerpt200;
	}

	public function getExcerpt400Attribute()
	{
		return $this->movie->excerpt400;
	}

	public function getScoreAttribute()
	{
		return $this->movie->score;
	}

}
