<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Repositories\MovieRepository;
use App\Library\ContentOnPage;
use Carbon\Carbon;

class MovieController extends Controller
{

    private $movieRepository;

	public function __Construct(MovieRepository $movieRepository, ContentOnPage $contentOnPage)
	{
        $this->movieRepository = $movieRepository;
        $this->contentOnPage = $contentOnPage;
	}
    
    public function tv($type, $channel, $time = 'cualquier-momento', $sort = 'destacadas')
    {
        $records = $this->movieRepository->getMovistar($type, $channel, $time, $sort);
        if ($records->isEmpty()) return view('empty');
        $records = $this->formatRecords($records);
        $parameters = ['type' => $type, 'channel' => $channel, 'time' => $time, 'sort' => $sort];
        $contentOnPage = $this->contentOnPage->getPage($parameters);
        return view('main', compact(['parameters', 'contentOnPage', 'records']));
    }


    public function netflix($type)
    {
        if ($type == 'peliculas') $type = 'movie';
        $records = $this->movieRepository->getNetflix($type);
        if ($records->isEmpty()) return view('empty');
        $records = $this->formatRecords($records);
        return view('main', compact(['type', 'records']));
    }

    public function bestNetflix($type)
    {
        if ($type == 'peliculas') $type = 'movie';
        $records = $this->movieRepository->getNetflix($type, 'best');
        if ($records->isEmpty()) return view('empty');
        $records = $this->formatRecords($records);
        return view('main', compact(['type', 'records']));
    }

    public function newNetflix($type)
    {
        if ($type == 'peliculas') $type = 'movie';
        $records = $this->movieRepository->getNetflix($type, 'new');
        if ($records->isEmpty()) return view('empty');
        $records = $this->formatRecords($records);
        return view('main', compact(['type', 'records']));
    }

    public function formatRecords($records)
    {
        $records_1 = $records->splice(0, 1)->first(); //1 elemento (sin colección)
        $records_2 = $records->splice(0, 1)->first(); //1 elemento (sin colección)
        $records_3 = $records->splice(0, 4); //4 elementos
        $records_4 = $records->splice(0, 7); //7 elementos
        $records_5 = $records->splice(0, 8); //8 elementos
        return compact('records_1', 'records_2', 'records_3', 'records_4', 'records_5');
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
