<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;

use Illuminate\Http\Request;

class TestController extends Controller
{
    public function test1()
    {
        $my_file = 'file.txt';
        $handle = fopen($my_file, 'w') or die ('Cannot open file:  '.$my_file);
        $data = 'test test test';
        fwrite($handle, $data);
        $storagePath = Storage::disk('s3')->put("testkkk.jpg", file_get_contents('https://agilepainrelief.com/wp-content/uploads/2011/07/photodune-9297562-example-stamp-xs.jpg'), 'public');
        dd($storagePath);
    }

    public function test2()
    {
        $contents = Storage::disk('s3')->get("testkkk.jpg");
        return view('test')->with(compact('contents'));
    }
}
