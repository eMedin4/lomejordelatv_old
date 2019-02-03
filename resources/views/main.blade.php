@extends('layouts.master')

@section('title', 'Page Title')
@section('bodyclass', 'main-page')

@section('content')

    <div class="main-header-wrap">
        @include('includes.topbar')
        @include('includes.intro')
        @include('includes.sidebar', $routeInfo)
    </div>

    <div class="content-wrap">
        <div class="wrapper">
            <div class="content-inner"> 
                <div class="main-content">   
                    <div class="grid">
                        <div class="grid-sizer"></div>
                        @foreach ($records as $record)
                            <article class="card-wrap card-wrap-3 grid-item {{$gridItemSize[$loop->index]}}">
                                @include('includes.card', [
                                    'records' => $records,
                                ]) 
                            </article>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection