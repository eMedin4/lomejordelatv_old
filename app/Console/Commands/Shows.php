<?php

namespace App\Console\Commands;

use App\Library\CreateShows;
use Illuminate\Console\Command;

class Shows extends Command
{
    // php artisan tv:show a 388 -e
    protected $signature = 'tv:shows {letter?} {page?} {--e|totheend} {--u|fullupdate}'; //la opcion e indica que empieze por la letra y pagina indicadas y que siga hasta el final del catalogo
    protected $description = 'Descarga catálogo de series de filmaffinity';
    protected $createShows;

    public function __construct(CreateShows $createShows)
    {
        parent::__construct();
        $this->createShows = $createShows;
    }

    public function handle()
    {
        $letter = $this->argument('letter');
        $page = $this->argument('page');
        $toTheEnd = $this->option('totheend');
        $fullUpdate = $this->option('fullupdate');
        $this->createShows->processAll($letter, $page, $toTheEnd, $fullUpdate);
    }
}
