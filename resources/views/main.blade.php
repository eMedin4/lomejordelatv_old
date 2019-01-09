@extends('layouts.master')

@section('title', 'Page Title')

@section('content')

    <div class="main-header-wrap">
        @include('includes.topbar')
        @include('includes.intro')
    </div>

    <div class="content-wrap">
        <div class="wrapper">
            <div class="content-inner">

                <div class="sidebar">
                    @include('includes.sidebar', $routeInfo)
                </div>
                
                <div class="main-page">   
                    <div class="grid">
                        <div class="grid-sizer"></div>
                        @foreach ($records as $record)
                            <article class="card-wrap card-wrap-3 grid-item {{$gridItemSize[$loop->index]}}">
                                @include('includes.card', [
                                    'record' => $record[0], //pasamos el item individual
                                    'recordCollection' => $record->sortByDesc('season'), //pasamos todos los items de esta pelicula (varios pases)
                                ]) 
                            </article>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection