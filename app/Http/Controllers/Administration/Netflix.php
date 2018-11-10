<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use Unirest\Request;

use App\Http\Controllers\Administration\Format;
use App\Http\Controllers\Administration\Repository;

class Netflix extends Controller
{

    private $repository;
    private $format;

    public function __Construct(Repository $repository, Format $format)
	{
        $this->repository = $repository;
        $this->format = $format;
	}

    public function movies()
    {

        $response = Request::get("https://unogs-unogs-v1.p.mashape.com/aaapi.cgi?q=-!1900,2018-!0,5-!0,10-!0-!Movie-!Any-!Any-!gt0-!&t=ns&cl=270&st=adv&ob=Relevance&p=1&sa=and",
            array(
                "X-Mashape-Key" => "gOFzFlQygSmshFnnjpES7LrlcxeCp1WbaeCjsncAAWjQpRy4hm",
                "X-Mashape-Host" => "unogs-unogs-v1.p.mashape.com"
            )
        );

        if ($response->code != 200 || $response->body->COUNT == 0) {
            echo 'Error o sin items en la respuestas de Netflix';
            return;
        }
        
        foreach ($response->body->ITEMS as $item) {
            //Si la encontramos en verificadas
            if (array_key_exists($item->netflixid, config('movies.netflix'))) {
                dd(config('movies.netflix')[$item->netflixid]);
            }
            $duration = $this->format->setMinutesFromHours($item->runtime);
            $this->repository->searchFromNetflix($item->title, $item->released, $duration);
            echo $item->title;
        }
        dd($response);
    }



    public function test()
    {

        $headers = array('Accept' => 'application/json');
        $query = array('foo' => 'hello', 'bar' => 'world');
        $response = Request::post('http://mockbin.com/request', $headers, $query);
        dd($response->code, $response->headers, $response->body, $response->raw_body);
    }



}