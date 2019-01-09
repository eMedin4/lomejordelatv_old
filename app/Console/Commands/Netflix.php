<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Library\Providers\Netflix as NetflixProvider;

class Netflix extends Command
{
    protected $signature = 'tv:netflix {--id=} {--p=} {--seasons}';
    protected $description = 'Descarga catalogo Netflix';
    private $netflix;

    public function __construct(NetflixProvider $netflix)
    {
        parent::__construct();
        $this->netflix = $netflix;
    }

    public function handle()
    {
        $id = $this->option('id');
        $pages = $this->option('p');
        $seasons = $this->option('seasons');

        //Si hemos introducido páginas
        if ($pages) {
            $pages = array_map('trim', explode(',', $pages));
            if (min($pages) >= 1 && min($pages) <= 40 && max($pages) >= 1 && max($pages) <=40) $this->netflix->runByPages($pages);
            else $this->info('Las páginas no son correctas');
            return;
        }

        //Si hemos introducido un id
        if ($id) $this->netflix->runId($id);

        //Si hemos introducido --seasons vamos a descargar unas cuantas temporadas de series
        if ($seasons) $this->netflix->getSeasons();

        //Si no recuperamos todo
        else $this->netflix->runAll();
    }
}
