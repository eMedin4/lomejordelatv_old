<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use App\Library\ContentOnPage;
use Illuminate\Support\Facades\Route;

class MovieComposer
{

    protected $users;
    private $contentOnPage;

    public function __construct(ContentOnPage $contentOnPage)
    {
        $this->contentOnPage = $contentOnPage;
    }

    public function compose(View $view)
    {

        
        $view->with([
            'gridItemSize' => $this->constructGridItemSize(), 
            'contentOnPage' => $this->constructContentOnPage(),
            'routeInfo' => $this->constructRouteInfo(),
        ]);
    }

    public function constructGridItemSize()
    {
        $gridItemSize = [];
        $m = [2,3,5,6,8,9,11,12,14,15,17,18,20,21,23,24,26,27,29,30,32,33,35,36,38,39,41,42,44,45,47,48,50,51];
        for ($i=0; $i < 100; $i++) { 
            //el primero en grande
            if ($i == 0) $gridItemSize[$i] = 'grid-item-x';
            //los de la izq que quedan debajo del primero 
            elseif (array_search($i, $m) !== false) $gridItemSize[$i] = 'grid-item-m';
            //el resto = la columna de la derecha
            else $gridItemSize[$i] = 'grid-item-l';
        }
        return $gridItemSize;
    }

    public function constructContentOnPage()
    {
        $contentOnPage = $this->contentOnPage->getPage($this->constructRouteInfo());
        return $contentOnPage;
    }

    public function constructRouteInfo()
    {
        $route['channel'] = Route::currentRouteName(); 
        $route['time'] = 'todas';
        $route['sort'] = 'destacadas';
        $route = array_merge($route, Route::current()->parameters());
        return $route;
    }
}