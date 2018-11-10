<?php
namespace App\Http\Controllers\Administration;

use Illuminate\Support\Facades\Storage;

class Dashboard
{

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

}