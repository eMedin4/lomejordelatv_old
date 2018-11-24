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
    Route::get('/setfromletter', 'Dashboard@setFromLetter')->name('setFromLetter');
    Route::get('/setfromfaid', 'Dashboard@setFromFaId')->name('setFromFaId');
    Route::get('/setfrommultiids', 'Dashboard@setFromMultiIds')->name('setFromMultiIds');
    Route::get('/netflix', 'Dashboard@netflix')->name('netflix');
    Route::get('/movistar', 'Dashboard@setMovistar')->name('setMovistar');
    Route::get('/amazon', 'amazonScraper@movies')->name('amazon');
    Route::get('/testing', 'Dashboard@testing')->name('testing');
});

/* 
    Usuario user user@gmail.com 123456
    Admin admin admin@gmail.com 123456

*/
Auth::routes();

Route::get('logout', 'MovieController@logout')->name('logout');
