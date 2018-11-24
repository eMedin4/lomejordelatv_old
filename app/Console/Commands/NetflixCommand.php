<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Library\Providers\Netflix;

class NetflixCommand extends Command
{
    protected $signature = 'tv:netflix';
    protected $description = 'Descarga catalogo Netflix';
    private $netflix;

    public function __construct(Netflix $netflix)
    {
        parent::__construct();
        $this->netflix = $netflix;
    }

    public function handle()
    {
        $this->netflix->getMovies('artisan');
    }
}
