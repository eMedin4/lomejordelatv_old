<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Repositories\MovieRepository;

use Carbon\Carbon;

class MovieController extends Controller
{

    private $movieRepository;

	public function __Construct(movieRepository $movieRepository)
	{
		$this->movieRepository = $movieRepository;
	}
    
    public function tv()
    {
        $records = $this->movieRepository->getMovistar();

        if ($records->isEmpty()) return view('empty');
        
        //records recientes
        $recentTime = Carbon::now()->addMinutes(60);
        $recentRecords = $records->where('time', '<', $recentTime)->sortBy('time');

        //todos los records divididos para el layout
        $records_1 = $records->splice(0, 1)->first(); //1 elemento (sin colección)
        $records_2 = $records->splice(0, 1)->first(); //1 elemento (sin colección)
        $records_3 = $records->splice(0, 4); //4 elementos
        $records_4 = $records->splice(0, 6); //8 elementos
        $records_5 = $records->splice(0, 4); //4 elementos
    	return view('main', compact('records_1', 'records_2', 'records_3', 'records_4', 'records_5', 'recentRecords'));
    }

    public function netflix()
    {
        $records = $this->movieRepository->getNetflix();

        if ($records->isEmpty()) return view('empty');

        //todos los records divididos para el layout
        $records_1 = $records->splice(0, 1)->first(); //1 elemento (sin colección)
        $records_2 = $records->splice(0, 1)->first(); //1 elemento (sin colección)
        $records_3 = $records->splice(0, 4); //4 elementos
        $records_4 = $records->splice(0, 6); //8 elementos
        $records_5 = $records->splice(0, 4); //4 elementos
    	return view('main', compact('records_1', 'records_2', 'records_3', 'records_4', 'records_5', 'recentRecords'));
    }

    public function show($slug)
    {
        setlocale(LC_TIME, config('app.locale'));
        $record = $this->movieRepository->getMovie($slug);
        return view('movie', compact('record'));
    }

    public function logout()
    {
        Auth::logout();
        return back();
    }
}
