<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Library\Providers\Netflix;

class NetflixDates extends Command
{
    protected $signature = 'tv:netflixdates';
    protected $description = 'Descarga catalogo Netflix: News and expiring';
    private $netflix;

    public function __construct(Netflix $netflix)
    {
        parent::__construct();
        $this->netflix = $netflix;
    }

    public function handle()
    {
        $this->netflix->getNew('artisan');
        $this->netflix->getExpiring('artisan');
    }
}
