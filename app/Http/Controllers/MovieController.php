<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Repositories\MovieRepository;


class MovieController extends Controller
{

    private $movieRepository;

	public function __Construct(MovieRepository $movieRepository)
	{
        $this->movieRepository = $movieRepository;
	}
    
    public function tv($type, $channel, $time = 'cualquier-momento', $sort = 'destacadas')
    {
        $records = $this->movieRepository->getMovistar($type, $channel, $time, $sort);
        $parameters = ['type' => $type, 'channel' => $channel, 'time' => $time, 'sort' => $sort];
        if ($records->isEmpty()) return view('empty', compact(['contentOnPage']));
        return view('main', compact(['parameters', 'records']));
    }


    public function netflix($type, $time = 'todas', $sort = 'destacadas', $fromyear = null, $toyear = null)
    {
        $records = $this->movieRepository->getNetflix($type, $time, $sort, $fromyear, $toyear);
        $parameters = ['type' => $type, 'channel' => 'netflix', 'time' => $time, 'sort' => $sort, 'fromYear' => $fromyear, 'toYear' => $toyear];
        if ($records->isEmpty()) return view('empty', compact(['contentOnPage']));
        return view('main', compact(['parameters', 'records']));
    }

    public function amazon($type, $sort = 'destacadas')
    {
        $records = $this->movieRepository->getAmazon($type, $sort);
        //$parameters = ['type' => $type, 'channel' => 'netflix', 'sort' => $sort];
        if ($records->isEmpty()) return view('empty', compact(['contentOnPage']));
        return view('main', compact(['records']));
    }

    public function hbo($type, $sort = 'destacadas')
    {
        $records = $this->movieRepository->getHbo($type, $sort);
        //$parameters = ['type' => $type, 'channel' => 'netflix', 'time' => $time, 'sort' => $sort, 'fromYear' => $fromyear, 'toYear' => $toyear];
        if ($records->isEmpty()) return view('empty', compact(['contentOnPage']));
        return view('main', compact(['records']));
    }



    public function processFiltersYearForm(Request $request, $type, $channel, $time = 'todas', $sort = 'destacadas')
    {
        //dd($type, $channel, $time, $sort, $request->all());
        $fromYear = $request->input('from-year');
        $toYear = $request->input('to-year');
        return redirect($type.'-'.$channel.'/'.$time.'/'.$sort.'/'.$fromYear.'/'.$toYear);
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
