<?php

namespace App\Console\Commands;

use App\Library\providers\Themoviedb as ThemoviedbProvider;
use Illuminate\Console\Command;

class Themoviedb extends Command
{

    protected $signature = 'tv:setseasons';
    protected $description = 'Command description';
    private $themoviedb;

    public function __construct(ThemoviedbProvider $themoviedb)
    {
        parent::__construct();
        $this->themoviedb = $themoviedb;
    }

    public function handle()
    {
        $this->themoviedb->setAllSeasons();
    }
}
