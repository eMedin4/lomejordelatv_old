<?php

namespace App\Console\Commands;

use App\Library\Providers\Movistar as MovistarProvider;
use Illuminate\Console\Command;

class Movistar extends Command
{

    protected $signature = 'tv:movistar';
    protected $description = 'Descarga programación Movistar';
    protected $movistar;

    public function __construct(MovistarProvider $movistar)
    {
        parent::__construct();
        $this->movistar = $movistar;
    }

    public function handle()
    {
        $this->movistar->getMovies('artisan');
        $this->info('Todo actualizado');
    }
}
