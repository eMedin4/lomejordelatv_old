<?php

Route::get('/temporal', 'TemporalRepository@temporal3');

Route::get('/', 'MovieController@tv')->name('tv');
Route::get('/netflix', ['as' => 'netflix', 'uses' => 'MovieController@netflix']);
Route::get('/pelicula/{slug}', 'MovieController@show')->name('movie');


/* ADMINISTRATION */

Route::group([
    'middleware' => ['auth', 'admin'],
    'namespace' => 'Administration',
    'prefix' => 'administration'
], function() {
    Route::get('/', 'Dashboard@show')->name('dashboard');
    Route::get('/clearCustomErrorsLog', 'Dashboard@clearCustomErrorsLog')->name('clearCustomErrorsLog');
    Route::get('/clearCustomMoviesLog', 'Dashboard@clearCustomMoviesLog')->name('clearCustomMoviesLog');
    Route::get('/filmaffinityalphabetically', 'MovieDatabaseBuilding@filmAffinityAlphabetically')->name('filmaffinityalphabetically');
    Route::get('/filmaffinityid', 'MovieDatabaseBuilding@filmAffinityId')->name('filmaffinityid');
    Route::get('/filmaffinitymultipleids', 'MovieDatabaseBuilding@filmAffinityMultipleIds')->name('filmaffinitymultipleids');
    Route::get('/netflix', 'Netflix@movies')->name('netflix');
    Route::get('/movistar', 'Movistar@movies')->name('movistar');
    Route::get('/amazon', 'amazonScraper@movies')->name('amazon');
    Route::get('/stick', 'MovieDatabaseBuilding@stick')->name('stick');
});

/* 
    Usuario user user@gmail.com 123456
    Admin admin admin@gmail.com 123456

*/
Auth::routes();

Route::get('logout', 'MovieController@logout')->name('logout');
