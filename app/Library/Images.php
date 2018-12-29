<?php
namespace App\Library;

use Image;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Library\Output;

class Images 
{

	private $output;

    public function __Construct(Output $output)
	{
        $this->output = $output;
    }

	public function savePoster($file, $slug) 
	{
		try {
			$url = 'http://image.tmdb.org/t/p/w1280' . $file;
			$image = Image::make($url)->fit(310, 465)->stream()->detach();
			Storage::disk('s3')->put('movieimages/posters/lrg/' . $slug . '.jpg', $image);
			return true;
		} catch (\Exception $e) {
			$this->output->message('Error en Image->savePoster: ' . $slug, true, 'error');
			return false;
		}
	}

	public function saveBackground($file, $slug) 
	{
		try {
			$url = 'http://image.tmdb.org/t/p/w1280' . $file;
			$image = Image::make($url)->fit(960, 540)->stream()->detach();
			Storage::disk('s3')->put('movieimages/backgrounds/lrg/' . $slug . '.jpg', $image, 'public');
			$image = Image::make($url)->fit(352, 198)->stream()->detach();
			Storage::disk('s3')->put('movieimages/backgrounds/sml/' . $slug . '.jpg', $image, 'public');
			//$this->output->message('Background guardado ok: ' . $slug, false);
			return true;
		} catch (\Exception $e) {
			$this->output->message('Error en Image->saveBackground: ' . $slug, false, 'error');
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

	public function saveFaToTemp($url, $id)
	{
		$image = Image::make($url)->stream()->detach();
		Storage::disk('s3')->put('movieimages/temp/' . $id . '.jpg', $image, 'public');
	}


}
