<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{

    public function boot()
    {
        // Using class based composers...
        View::composer(
            'main', 'App\Http\View\Composers\MovieComposer'
        );
    }

    public function register()
    {
        //
    }
}
