<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Library\Providers\Netflix;

class NetflixSingleCommand extends Command
{
    protected $signature = 'tv:netflixsingle {nfid}';
    protected $description = 'Descarga item de Netflix';
    private $netflix;

    public function __construct(Netflix $netflix)
    {
        parent::__construct();
        $this->netflix = $netflix;
    }

    public function handle()
    {
        $nfid = $this->argument('nfid');
        $this->netflix->getMovie('artisan', $nfid);
    }
}
