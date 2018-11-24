<?php
namespace App\Http\Controllers\Administration;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Library\MixMovies;
use App\Library\Testing;
use App\Library\Providers\Movistar;
use App\Library\Providers\Netflix;
	

class Dashboard
{
    private $request;
    private $mixMovies;
    private $movistar;
    private $testing;
    private $netflix;

    public function __Construct(Request $request, MixMovies $mixMovies, Movistar $movistar, Testing $testing, Netflix $netflix)
	{
        $this->request = $request;
        $this->mixMovies = $mixMovies;
        $this->movistar = $movistar;
        $this->testing = $testing;
        $this->netflix = $netflix;
    }

    public function show()
    {
        $customErrors = Storage::disk('local')->get('customErrors.log');
        $customMovies = Storage::disk('local')->get('customMovies.log');
        return view('administration.dashboard', compact('customErrors', 'customMovies'));
    }
    
    public function clearCustomErrorsLog()
    {
        Storage::disk('local')->put('customErrors.log', '');
        return redirect()->route('dashboard');
    }

    public function clearCustomMoviesLog()
    {
        Storage::disk('local')->put('customMovies.log', '');
        return redirect()->route('dashboard');
    }

    public function setFromFaId()
    {
        $faid = trim($this->request->input('faid'));
        if (substr( $faid, 0, 4 ) !== "film") $faid = "film" . $faid;
        $this->mixMovies->setFromFaId('browse', $faid);
    }

    public function setFromMultiIds()
    {
        $faids = explode(',', $this->request->input('faids'));
        foreach($faids as $faid) {
			if (substr( trim($faid), 0, 4 ) !== "film") $faid = "film" . $faid;
            $this->mixMovies->setFromFaId('browse', $faid);
        }
    }

    public function setFromLetter()
    {
        $letter = $this->request->input('letter');
		if ($this->request->input('first-page')) $firstPage = $this->request->input('first-page');
        else $firstPage = 1;
        if ($this->request->input('total-pages')) $totalPages = $this->request->input('total-pages');
        else $totalPages = 1000;
        $this->mixMovies->setFromLetter('browse', $letter, $firstPage, $totalPages);
    }

    public function setMovistar()
    {
        $this->movistar->getMovies('browse');
    }

    public function testing()
    {
        //boton SEARCH
        if ($this->request->has('search')) {
            $faid = trim($this->request->input('faid'));
            $details = $this->request->input('details');
            if (substr( $faid, 0, 4 ) !== "film") $faid = "film" . $faid;
            $data = $this->testing->faTmTest($faid, 'browse', $details, $more = false);
            return view('administration.testing', compact('data', 'details'));
        }

        //boton MORE
        if ($this->request->has('more')) {
            $faid = trim($this->request->input('faid'));
            $details = $this->request->input('details');
            if (substr( $faid, 0, 4 ) !== "film") $faid = "film" . $faid;
            $data = $this->testing->faTmTest($faid, 'browse', $details, $more = true);
            return view('administration.testing', compact('data', 'details'));
        }

        //boton VERIFY
        if ($this->request->has('verify')) {
            //format filmaffinity id
            $faid = trim($this->request->input('faidpermanent'));
            if (substr( $faid, 0, 4 ) !== "film") $faid = "film" . $faid;
            //tmid custom o normal
            if ($this->request->input('tm_id') == 'custom') $tmid = $this->request->input('customtmid');
            else $tmid = $this->request->input('tm_id');
            $responseMessage = $this->testing->setFaTest($faid, 'tm', $tmid);
            return view('administration.testing', compact('responseMessage'));
        }
        return view('administration.testing');
    }

    public function netflix()
    {
        $movies = $this->netflix->getMovies('browse');
    }



}