<?php

//Route::redirect('/', '/películas-en-tv'); Esto es una redireccion 301 pero en local no funciona porque redirecciona a localhost/peliculas-en-tv. Comprobar en servidor

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


Route::get('/{type}-netflix/{list?}', 'MovieController@netflix')
    ->where([
        'type' => 'series|peliculas',
        'list' => 'trending|nuevas|expiran|mejores|populares', 
        ])
    ->name('netflix');

Route::get('/{type}-amazon/{list?}/', 'MovieController@amazon')
    ->where([
        'type' => 'series|peliculas',
        'list' => 'trending|mejores|populares',
        ])
    ->name('amazon');

Route::get('/{type}-hbo/{list?}', 'MovieController@hbo')
    ->where([
        'type' => 'series|peliculas',
        'list' => 'trending|mejores|populares',
        ])
    ->name('hbo');






/*
Route::get('/{type}-de-netflix', 'MovieController@netflix')->where(['type' => 'series|peliculas'])->name('netflix');
Route::get('/mejores-{type}-de-netflix', 'MovieController@bestNetflix')->where(['type' => 'series|peliculas'])->name('bestNetflix');
Route::get('/nuevas-{type}-de-netflix', 'MovieController@newNetflix')->where(['type' => 'series|peliculas'])->name('newNetflix');

*/

Route::post('/livesearch', 'MovieController@liveSearch')->name('liveSearch');
Route::get('/titulo/{slug}', 'MovieController@show')->name('movie');





/* ADMINISTRATION */

Route::group([
    'middleware' => ['auth', 'admin'],
    'namespace' => 'Administration',
    'prefix' => 'administration',
    'as' => 'administration.',
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


    Route::get('/testing', 'Dashboard@testing')->name('testing');;
    Route::get('/managemovies/{provider}', 'ManageMovies@index')
    ->where([
        'provider' => 'netflix|amazon|hbo',
        ])
    ->name('manageMovies');
    Route::post('/managemovies', 'ManageMovies@store')->name('postManageMovies');
});

/* 
    Usuario user user@gmail.com 123456
    Admin admin admin@gmail.com 123456

*/
Auth::routes();

Route::get('logout', 'MovieController@logout')->name('logout');
