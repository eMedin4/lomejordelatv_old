@extends('layouts.layout')

@section('title', 'Page Title')

@section('content')

    <div class="header-wrap">
        @include('topbar')
    </div>

    <div class="content-wrap">
        <div class="wrapper">
            <div class="item-page">
                @yield('content')
            </div>
        </div>
    </div>

@endsection