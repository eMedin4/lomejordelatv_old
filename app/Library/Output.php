<?php
namespace App\Library;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Output\ConsoleOutput;

class Output
{
    private $console;

    public function __Construct(ConsoleOutput $console)
	{
		$this->console = $console;
	}
    
    //type = info (default), line, comment, question, error
    public function message($message, $log, $type = 'info')
    {
        if ($log) $this->writeLog($message);
        $this->writeArtisan($message, $type);
    }
    
    public function writeLog($message)
    {
        Log::channel('customErrors')->debug($message);
    }
    
    public function writeArtisan($message, $type)
    {
        if ($type == 'info') {
            $this->console->writeln("<info>" . $message . "</info>");
        } elseif ($type == 'line') {
            $this->console->write("<comment>" . $message . "</comment>");
        } elseif ($type == 'comment') {
            $this->console->writeln("<comment>" . $message . "</comment>");
        } elseif ($type == 'question') {
            $this->console->writeln("<question>" . $message . "</question>");
        } elseif ($type == 'error') {
            $this->console->writeln("<error>" . $message . "</error>");
        }
    }

}