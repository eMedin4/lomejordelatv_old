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
    
    public function message($message, $log, $source, $breakLine = true)
    {
        if ($log) $this->writeLog($message);
        if ($source == 'browse') $this->writeBrowse($message, $breakLine);
        if ($source == 'artisan') $this->writeArtisan($message, $breakLine);
    }
    
    public function writeLog($message)
    {
        Log::channel('customErrors')->debug($message);
    }
    
    
    public function writeBrowse($message, $breakLine)
    {
        if ($breakLine == true) {
            echo $message . "<br>";
        } else {
            echo $message;
        }
    }
    
    
    public function writeArtisan($message, $breakLine)
    {

        if ($breakLine == true) {
            $this->console->writeln("<info>" . $message . "</info>");
        } else {
            $this->console->write("<comment>" . $message . "</comment>");
        }
    }

}