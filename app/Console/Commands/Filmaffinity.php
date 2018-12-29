<?php

namespace App\Console\Commands;

use App\Library\ItemCreation;
use Illuminate\Console\Command;

class Filmaffinity extends Command
{
    // php artisan tv:show a 388 -e
    protected $signature = 'tv:filmaffinity {letter?} {page?} {--e|totheend} {--u|fullupdate} {--id=} {--seasons}'; //la opcion e indica que empieze por la letra y pagina indicadas y que siga hasta el final del catalogo
    protected $description = 'Descarga del catálogo de filmaffinity';
    protected $itemCreation;

    public function __construct(ItemCreation $itemCreation)
    {
        parent::__construct();
        $this->itemCreation = $itemCreation;
    }

    public function handle()
    {
        $seasons = $this->option('seasons');
        if ($seasons) $this->itemCreaton->seasons();

        //si hemos indicado un ID scrapeamos solo este id
        $id = $this->option('id');
        if ($id) { 
            if (substr( $id, 0, 4 ) !== "film") $id = "film" . $id;
            $this->itemCreation->runId($id);

        //si no scrapeamos las paginas indicadas
        } else {
            $letter = $this->argument('letter');
            $page = $this->argument('page');
            $toTheEnd = $this->option('totheend');
            $fullUpdate = $this->option('fullupdate');
            $this->itemCreation->run($letter, $page, $toTheEnd, $fullUpdate);
        }

    }
}
