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


    public function netflix($type, $list = 'recomendadas')
    {
        $records = $this->movieRepository->getNetflix($type, $list);
        $recordsCount = $this->movieRepository->getNetflixCount($type);
        if ($records->isEmpty()) return view('empty');
        return view('main', compact(['records', 'recordsCount']));
    }

    public function amazon($type, $sort = 'recomendadas')
    {
        $records = $this->movieRepository->getAmazon($type, $sort);
        $recordsCount = $this->movieRepository->getAmazonCount($type);
        if ($records->isEmpty()) return view('empty');
        return view('main', compact(['records', 'recordsCount']));
    }

    public function hbo($type, $sort = 'recomendadas')
    {
        $records = $this->movieRepository->getHbo($type, $sort);
        $recordsCount = $this->movieRepository->getHboCount($type);
        if ($records->isEmpty()) return view('empty');
        return view('main', compact(['records', 'recordsCount']));
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

    public function liveSearch(Request $request)
    {
    	if( ! $request->ajax()) {       
            return back(); 
        }

        $this->validate($request, [
	        'string' => 'required|max:50'
        ]);
        
        $results = $this->movieRepository->liveSearch($request->input('string'));
        
        if ($results->isEmpty()) {
            return response()->json(['response' => false]);
        }
        
        return response()->json(['response' => true, 'result' => $results]);
	}

}
