<?php

namespace App\Console\Commands;

use App\Library\MixMovies;
use Illuminate\Console\Command;

class FaIdCommand extends Command
{

    protected $signature = 'tv:faid {faid}';
    protected $description = 'Descarga desde id de filmaffinity';
    protected $mixMovies;

    public function __construct(MixMovies $mixMovies)
    {
        parent::__construct();
        $this->mixMovies = $mixMovies;
    }

    public function handle()
    {
        $faid = $this->argument('faid');
        if (substr( $faid, 0, 4 )  !== "film") $faid = "film" . $faid;
        $this->mixMovies->setFromFaId( 'artisan', $faid );
    }
}
