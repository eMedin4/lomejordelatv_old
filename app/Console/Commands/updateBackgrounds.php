<?php

namespace App\Console\Commands;

use App\Library\providers\Themoviedb;
use Illuminate\Console\Command;

class updateBackgrounds extends Command
{

    protected $signature = 'tv:updatebackgrounds';
    protected $description = 'Command description';
    private $themoviedb;

    public function __construct(Themoviedb $themoviedb)
    {
        parent::__construct();
        $this->themoviedb = $themoviedb;
    }

    public function handle()
    {
        $this->themoviedb->updateBackgrounds('artisan');
    }
}
