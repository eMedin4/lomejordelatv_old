<?php
namespace App\Http\Controllers\Administration;

use Image;
use Illuminate\Support\Facades\Log;

class Images 
{

	public function savePoster($file, $slug) 
	{
		try {
			$url = 'http://image.tmdb.org/t/p/w1280' . $file;
			Image::make($url)->fit(310, 465)->save(public_path() . '/moviesimg/posters/lrg/' . $slug . '.jpg');
			return true;
		} catch (\Exception $e) {
			Log::channel('customErrors')->debug('Error en Image->savePoster: ' . $slug);
			return false;
		}
	}

	public function saveBackground($file, $slug) 
	{
		try {
			$url = 'http://image.tmdb.org/t/p/w1280' . $file;
			Image::make($url)->fit(960, 540)->save(public_path() . '/moviesimg/backgrounds/lrg/' . $slug . '.jpg');
			Image::make($url)->fit(352, 198)->save(public_path() . '/moviesimg/backgrounds/sml/' . $slug . '.jpg');
			return true;
		} catch (\Exception $e) {
			Log::channel('customErrors')->debug('Error en Image->saveBackground: ' . $slug);
			return false;
		}
	}

	public function saveCredit($file, $name, $movie_id)
	{
		try {
			$url = 'http://image.tmdb.org/t/p/w185' . $file;
			Image::make($url)->save(public_path() . '/moviesimg/credits' . $file);
			return true;
		} catch (\Exception $e) {
			return false;
		}		
	}


}
