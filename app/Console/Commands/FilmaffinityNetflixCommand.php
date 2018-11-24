<?php

namespace App\Console\Commands;

use App\Library\MixMovies;
use Illuminate\Console\Command;

class FilmaffinityNetflixCommand extends Command
{

    protected $signature = 'tv:filmaffinitynetflix';
    protected $description = 'Descargar peliculas de filmaffinity > netflix';
    protected $mixMovies;
    
    public function __construct(MixMovies $mixMovies)
    {
        parent::__construct();
        $this->mixMovies = $mixMovies;
    }

    public function handle()
    {
        $this->mixMovies->FilmaffinityNetflixNew( 'artisan');
        $this->mixMovies->FilmaffinityNetflixUpcoming( 'artisan');
    }
}
