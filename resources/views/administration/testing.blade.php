@extends('administration.layout')

@section('content')
<form method="GET" action="{{route('testing')}}">
    @csrf
    @if (isset($responseMessage))
        <div class="message"><span>{{$responseMessage}}</span></div>
    @endif
    <div class="content-bar">
        <input type="checkbox" name="details" value="details"><label for="details">Con detalles</label>
        <input type="text" name="faid" placeholder="filmaffinity id">
        <button type="submit" name="search">Buscar</button>
        <button type="submit" name="more">Más resultados</button>
        <button type="submit" name="verify">Relacionar</button>
    </div>
    <div class="content content-testing">
        <!--BASE DE DATOS FILMAFFINITY-->
        <div class="card-wrap">
            <div class="card">
                <h3>Base de datos (Filmaffinity)</h3>
                @if (isset($data['db']) && $data['db'] == true)
                    <span class="id">{{$data['db']->fa_id}}</span>
                    <span class="title">{{$data['db']->title}} <i>{{$data['db']->original_title}}</i></span>
                    <span class="meta">{{$data['db']->year}} {{$data['db']->country}}</span>
                @else
                    <span class="empty">No se encuentra</span>
                @endif
            </div>
        </div>

        <!--BASE DE DATOS THEMOVIEDB-->
        <div class="card-wrap">
            <div class="card">
                <h3>Base de datos (Themoviedb)</h3>
                @if (isset($data['db']) && $data['db'] == true && $data['db']->tm_id)
                    <span class="id">{{$data['db']->tm_id}}</span>
                    <span class="director">
                        @foreach ($data['db']->characters as $character)
                            @if ($character->department == 'director')
                                {{$character->name}} 
                            @endif
                        @endforeach
                    </span>
                    @if ($data['db']->check_poster)
                    <img src="https://s3.eu-west-3.amazonaws.com/lomejordelatv/movieimages/posters/lrg/{{ $data['db']->slug }}.jpg">
                    @endif
                    @if ($data['db']->check_background)
                    <img src="https://s3.eu-west-3.amazonaws.com/lomejordelatv/movieimages/backgrounds/sml/{{ $data['db']->slug }}.jpg">
                    @endif
                @else
                    <span class="empty">No se encuentra</span>
                @endif
            </div>
        </div>

        <!--SCRAP FILMAFFINITY-->
        <div class="card-wrap">
            <div class="card">
                <h3>Filmaffinity (web)</h3>
                @if (isset($data['fa']))
                    @if ($data['fa']['response'] == true)
                        <a class="id" href="https://www.filmaffinity.com/es/film{{$data['fa']['fa_id']}}.html">{{$data['fa']['fa_id']}}</a>
                        <span class="title">{{$data['fa']['fa_title']}} <i>{{$data['fa']['fa_original']}}</i></span>
                        <span class="director">{{$data['fa']['fa_director']}}</span>
                        <span class="meta">{{$data['fa']['fa_year']}} {{$data['fa']['fa_country']}}</span>
                        <span class="metatype">{{$data['fa']['fa_type']}}</span>
                        <img src="https://s3.eu-west-3.amazonaws.com/lomejordelatv/movieimages/temp/{{$data['fa']['fa_id']}}.jpg">
                        <input type="hidden" name="faidpermanent" value="{{$data['fa']['fa_id']}}">
                    @else
                        <span class="empty">{{$data['fa']['message']}}</span>
                    @endif
                @endif
            </div>
        </div>
        <!--API THEMOVIEDB-->
        <div class="card-wrap">
            <div class="card">
                <h3>Themoviedb (api)</h3>
                @if (isset($data['tm']))  
                    @if ($details)
                        @foreach ($data['tm'] as $movie)
                            <div class="card-inner">
                                <input type="radio" name="tm_id" value="{{$movie['tm_id']}}">
                                @if ($movie['tm_type'] == 'movie')
                                    <a class="id" href="https://www.themoviedb.org/movie/{{$movie['tm_id']}}">{{$movie['tm_id']}}</a>
                                @else
                                    <a class="id" href="https://www.themoviedb.org/tv/{{$movie['tm_id']}}">{{$movie['tm_id']}}</a>
                                @endif
                                <span class="title">{{$movie['tm_title']}} <i>{{$movie['tm_original']}}</i></span>
                                <span class="meta">{{$movie['tm_year']}}
                                    @if ($movie['tm_type'] == 'movie')
                                        @if ($movie['tm_countries'])
                                            @foreach ($movie['tm_countries'] as $country)
                                                {{$country->name}} 
                                            @endforeach
                                        @endif
                                    @endif
                                </span>
                                @if ($movie['tm_type'] == 'movie')
                                    <span class="director">
                                        @if ($movie['credits'])
                                            @foreach ($movie['credits']->crew as $credit)
                                                @if ($credit->department == 'Directing')
                                                    {{$credit->name}} 
                                                @endif
                                            @endforeach
                                        @endif
                                    </span>    
                                @endif                          
                                <div>
                                    <img class="poster-tmdb" src="http://image.tmdb.org/t/p/w1280{{$movie['poster']}}">
                                    <img class="background-tmdb" src="http://image.tmdb.org/t/p/w1280{{$movie['background']}}">
                                </div>
                            </div>
                        @endforeach
                    @else
                        @foreach ($data['tm'] as $movie)
                            {{--dd($movie)--}}
                            <div class="card-inner">
                                <input type="radio" name="tm_id" value="{{$movie['tm_id']}}">
                                <a class="id" href="https://www.themoviedb.org/movie/{{$movie['tm_id']}}">{{$movie['tm_id']}}</a>
                                <span class="title">{{$movie['tm_title']}} <i>{{$movie['tm_original']}}</i></span>
                                <span class="meta">{{$movie['tm_year']}}</span>
                                <div>
                                    <img class="poster-tmdb" src="http://image.tmdb.org/t/p/w1280{{$movie['poster']}}">
                                    <img class="background-tmdb" src="http://image.tmdb.org/t/p/w1280{{$movie['background']}}">
                                </div>
                            </div>
                        @endforeach
                    @endif
                    <div>
                        <input type="radio" name="tm_id" value="custom">
                        <input type="text" name="customtmid" placeholder="tmdb id">
                    </div>
                @endif
            </div>
        </div>
    </div>
</form>
@endsection