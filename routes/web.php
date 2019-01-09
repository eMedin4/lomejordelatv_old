<?php

//Route::redirect('/', '/películas-en-tv'); Esto es una redireccion 301 pero en local no funciona porque redirecciona a localhost/peliculas-en-tv. Comprobar en servidor

Route::get('/amazon', 'TestController@amazon');

Route::get('/', function(){
    return redirect()->route('tv', ['type' => 'peliculas', 'channel' => 'tv']);
});

Route::get('/{type}-{channel}/{time?}/{sort?}', 'MovieController@tv')
    ->where([
        'type' => 'series|peliculas',
        'channel' => 'tv|tdt|canales-de-pago',
        'time' => 'cualquier-momento|hoy|ahora|esta-noche|manana', 
        'sort' => 'destacadas|populares|mejores',
        ])
    ->name('tv');


Route::get('/{type}-netflix/{time?}/{sort?}', 'MovieController@netflix')
    ->where([
        'type' => 'series|peliculas',
        'time' => 'todas|nuevas|expiran', 
        'sort' => 'destacadas|populares|mejores',
        ])
    ->name('netflix');

Route::get('/amazon-{type}/{sort?}/', 'MovieController@amazon')
    ->where([
        'type' => 'series|peliculas',
        'sort' => 'destacadas|populares|mejores',
        ])
    ->name('amazon');

Route::get('/hbo-{type}/{sort?}', 'MovieController@hbo')
    ->where([
        'type' => 'series|peliculas',
        'sort' => 'destacadas|populares|mejores',
        ])
    ->name('hbo');

Route::post('/processFiltersYearForm/{type}/{channel}/{time?}/{sort?}', 'MovieController@processFiltersYearForm')->name('processFiltersYearForm');




/*
Route::get('/{type}-de-netflix', 'MovieController@netflix')->where(['type' => 'series|peliculas'])->name('netflix');
Route::get('/mejores-{type}-de-netflix', 'MovieController@bestNetflix')->where(['type' => 'series|peliculas'])->name('bestNetflix');
Route::get('/nuevas-{type}-de-netflix', 'MovieController@newNetflix')->where(['type' => 'series|peliculas'])->name('newNetflix');

*/

Route::get('/pelicula/{slug}', 'MovieController@show')->name('movie');





/* ADMINISTRATION */

Route::group([
    'middleware' => ['auth', 'admin'],
    'namespace' => 'Administration',
    'prefix' => 'administration'
], function() {
    Route::get('testnetflix/{page}', 'Dashboard@testNetflix')->name('testNetflix');



    Route::get('/', 'Dashboard@show')->name('dashboard');
    Route::get('/clearCustomErrorsLog', 'Dashboard@clearCustomErrorsLog')->name('clearCustomErrorsLog');
    Route::get('/clearCustomMoviesLog', 'Dashboard@clearCustomMoviesLog')->name('clearCustomMoviesLog');
    Route::get('/setfromletter', 'Dashboard@setFromLetter')->name('setFromLetter');
    Route::get('/setfromfaid', 'Dashboard@setFromFaId')->name('setFromFaId');
    Route::get('/setfrommultiids', 'Dashboard@setFromMultiIds')->name('setFromMultiIds');
    Route::get('/netflix', 'Dashboard@netflix')->name('setNetflix');
    Route::get('/movistar', 'Dashboard@setMovistar')->name('setMovistar');
    Route::get('/amazon', 'amazonScraper@movies');
    Route::get('/testing', 'Dashboard@testing')->name('testing');
});

/* 
    Usuario user user@gmail.com 123456
    Admin admin admin@gmail.com 123456

*/
Auth::routes();

Route::get('logout', 'MovieController@logout')->name('logout');
