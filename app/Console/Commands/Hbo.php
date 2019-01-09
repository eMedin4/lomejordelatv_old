<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Library\Providers\Hbo as HboProvider;

class Hbo extends Command
{
    protected $signature = 'tv:hbo';
    protected $description = 'Descarga catalogo Hbo';
    private $hbo;

    public function __construct(HboProvider $hbo)
    {
        parent::__construct();
        $this->hbo = $hbo;
    }

    public function handle()
    {
        $this->hbo->run();
    }
}
