@extends('administration.layout')

@section('content')
<div class="content">
    @if (session('status'))
        <div class="message">
            {{ session('status') }}
        </div>
    @endif
    <div class="card">
        <form method="GET" action="{{route('setFromLetter')}}">
            @csrf
            <p>Scrapeamos películas desde Filmaffinity por letra</p>
            <div class="inputs-align">
                <input type="text" name="letter" placeholder="Letra">
                <input type="text" name="first-page" placeholder="Pag inicio">
                <input type="text" name="total-pages" placeholder="Pag totales">
            </div>
            <button type="submit">Empezar</button>
            <small>Si no indicamos Pag inicio empezará en la 1, si no indicamos Pag totales acabará en la última</small>
        </form>
    </div>

    <div class="card">
        <form method="GET" action="{{route('setFromFaId')}}">
            @csrf
            <p>Scrapeamos una película</p>
            <div class="inputs-align">
                <input type="text" name="faid" placeholder="Filmaffinity Id">
            </div>
            <button type="submit">Empezar</button>
        </form>
    </div>

    <div class="card">
        <form method="GET" action="{{route('setFromMultiIds')}}">
            @csrf
            <p>Scrapeamos varias películas</p>
            <div class="inputs-align">
                <textarea name="faids" cols="30" rows="3" placeholder="Filmaffinity ids separados por coma"></textarea>
            </div>
            <button type="submit">Empezar</button>
        </form>
    </div>

    <div class="card">
        <form method="GET" action="{{route('setMovistar')}}">
            @csrf
            <p>Scrapeamos guia de movistar</p>
            <button type="submit">Empezar</button>
        </form>
    </div>

    <div class="card">
        <form method="GET" action="{{route('testing')}}">
            @csrf
            <p>Testing</p>
            <small>Busca coincidencias en themoviedb</small>
            <div class="inputs-align">
                <input type="text" name="faid" placeholder="Filmaffinity Id">
                <button type="submit">Empezar</button>
            </div>
        </form>
    </div>

    <div class="card">
        <form method="GET" action="{{route('netflix')}}">
            @csrf
            <p>Netflix</p>
            <small>Descarga catálogo de Netflix</small>
            <div class="inputs-align">
                <button type="submit">Empezar</button>
            </div>
        </form>
    </div>
</div>


<div class="content-full-width">
    <div class="card">
        <form method="GET" action="{{route('clearCustomErrorsLog')}}">
            <p>Log customErrors</p>
            <div class="text-from-file">
                {!! nl2br(e($customErrors)) !!}
            </div>
            <button type="submit">Borrar todo</button>
        </form>
    </div>
</div>
<div class="content-full-width">
    <div class="card">
        <form method="GET" action="{{route('clearCustomMoviesLog')}}">
            <p>Log customMovies</p>
            <div class="text-from-file">
                {!! nl2br(e($customMovies)) !!}
            </div>
            <button type="submit">Borrar todo</button>
        </form>
    </div>
</div>
@endsection