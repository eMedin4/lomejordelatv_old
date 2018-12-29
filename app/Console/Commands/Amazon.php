<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Library\Providers\Amazon as AmazonProvider;

class Amazon extends Command
{
    protected $signature = 'tv:amazon';
    protected $description = 'Descarga catalogo Amazon';
    private $amazon;

    public function __construct(AmazonProvider $amazon)
    {
        parent::__construct();
        $this->amazon = $amazon;
    }

    public function handle()
    {
        $this->amazon->run();
    }
}
