<?php

namespace App\Console\Commands;

use App\Http\Controllers\Administration\Movistar;
use Illuminate\Console\Command;

class MovistarCommand extends Command
{

    protected $signature = 'lmtv:movistar';

    protected $description = 'Descarga programación Movistar';

    private $movistar;

    public function __construct(Movistar $movistar)
    {
        parent::__construct();
        $this->movistar = $movistar;
    }

    public function handle()
    {
        $this->movistar->setCommand($this);
        $this->movistar->movies();
        $this->info('perfecto');
    }
}
