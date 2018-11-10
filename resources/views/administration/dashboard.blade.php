@extends('administration.layout')

@section('content')
<div class="content">
    @if (session('status'))
        <div class="message">
            {{ session('status') }}
        </div>
    @endif
    <div class="card">
        <form method="GET" action="{{route('filmaffinityalphabetically')}}">
            @csrf
            <p>Scrapeamos películas desde Filmaffinity por letra</p>
            <div class="inputs-align">
                <input type="text" name="letter" placeholder="Letra">
                <input type="text" name="first-page" placeholder="Pag inicio">
                <input type="text" name="total-pages" placeholder="Pag totales">
            </div>
            <button type="submit">Empezar</button>
            <label class="myCheckbox">
                <input type="checkbox" name="debug"/>
                <span></span>
                <div>Modo debug</div>
            </label>
            <small>Si no indicamos Pag inicio empezará en la 1, si no indicamos Pag totales acabará en la última</small>
        </form>
    </div>

    <div class="card">
        <form method="GET" action="{{route('filmaffinityid')}}">
            @csrf
            <p>Scrapeamos una película</p>
            <div class="inputs-align">
                <input type="text" name="faid" placeholder="Filmaffinity Id">
            </div>
            <button type="submit">Empezar</button>
            <label class="myCheckbox">
                <input type="checkbox" name="debug"/>
                <span></span>
                <div>Modo debug</div>
            </label>
        </form>
    </div>

    <div class="card">
        <form method="GET" action="{{route('filmaffinitymultipleids')}}">
            @csrf
            <p>Scrapeamos varias películas</p>
            <div class="inputs-align">
                <textarea name="faids" cols="30" rows="3" placeholder="Filmaffinity ids separados por coma"></textarea>
            </div>
            <button type="submit">Empezar</button>
            <label class="myCheckbox">
                <input type="checkbox" name="debug"/>
                <span></span>
                <div>Modo debug</div>
            </label>
        </form>
    </div>

    <div class="card">
        <form method="GET" action="{{route('movistar')}}">
            @csrf
            <p>Scrapeamos guia de movistar</p>
            <button type="submit">Empezar</button>
        </form>
    </div>
    <div class="card">
        <form method="GET" action="{{route('stick')}}">
            @csrf
            <p>Vinculamos película</p>
            <div class="inputs-align">
                <select name="source1">
                  <option value="fa" selected>Filmaffinity</option> 
                  <option value="tm">The Movie Db</option>
                  <option value="im">Imdb</option>
                  <option value="nf">Netflix</option>
                </select>
                <input type="text" name="id1" placeholder="id 1">
                <select name="source2">
                  <option value="fa">Filmaffinity</option> 
                  <option value="tm" selected>The Movie Db</option>
                  <option value="im">Imdb</option>
                  <option value="nf">Netflix</option>
                </select>
                <input type="text" name="id2" placeholder="id 2">
            </div>
            <button type="submit">Adherir</button>
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