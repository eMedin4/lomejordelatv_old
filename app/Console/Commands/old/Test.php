<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Library\Algorithm;

class Test extends Command
{
    protected $signature = 'tv:test {year?} {count?}';
    protected $description = 'test';
    private $alg;

    public function __construct(Algorithm $alg)
    {
        parent::__construct();
        $this->alg = $alg;
    }

    public function handle()
    {
        $year = $this->argument('year');
        $count = $this->argument('count');

        if (!$year) {
            $this->alg->setPopularity();
        }

        $response = $this->alg->popularityMovie($year, $count);
        $this->info($response);
    }
}
