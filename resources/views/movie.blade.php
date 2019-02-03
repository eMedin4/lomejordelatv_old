@extends('layouts.master')

@section('title', 'Page Title')
@section('bodyclass', 'item-page')

@section('content')

    <div class="main-header-wrap">
        @include('includes.topbar')
    </div>

    <div class="content-wrap">
        <div class="wrapper">
            <article class="card-wrap">
                <div class="card">

                    <div class="card-meta">
                        @if ($record->check_background)
                            <div class="thumb">
                                <img src="https://s3.eu-west-3.amazonaws.com/lomejordelatv/movieimages/backgrounds/lrg/{{ $record->slug }}.jpg" alt="">
                            </div>
                            @if ($record->check_poster)
                                <div class="poster">
                                    <img src="https://s3.eu-west-3.amazonaws.com/lomejordelatv/movieimages/posters/lrg/{{ $record->slug }}.jpg" alt="">
                                </div>
                            @else
                                <div class="no-poster"></div>
                            @endif
                        @else
                            <div class="no-thumb">
                                @if ($record->check_poster)
                                    <div class="poster">
                                        <img src="https://s3.eu-west-3.amazonaws.com/lomejordelatv/movieimages/posters/lrg/{{ $record->slug }}.jpg" alt="">
                                    </div>
                                @else
                                    <div class="no-poster"></div>
                                @endif
                            </div>
                        @endif
                    </div>

                    <div class="card-title"><h1>{{ $record->title }}</h1></div>
                    
                    <div class="card-extra">
                        <ul class="card-tags">
                            <li class="card-tags-details">
                                <span class="country country-{{ str_slug($record->country) }}"></span>
                                {{ $record->original_title }}
                            </li>
                            <li class="break">
                                {{ $record->year }}
                            </li>
                            <li class="break">
                                {{ $record->duration }} mins.
                            </li>
                            <li class="break">
                                @foreach ($record->genres as $genre)
                                {{$genre->name}}
                                @endforeach
                            </li>
                        </ul>
                    </div>
                    
                    <div class="card-review"><p>{{ $record->review }}</p></div>

                    <div class="characters">
                        @if ($record->directors->count())
                        <p class="actors">
                            <span class="intro">Director: </span>
                            @foreach($record->directors as $director)
                                @if ($loop->last)
                                    <a href="#">{{$director->name}}</a>.
                                @else
                                    <a href="#">{{$director->name}}</a>, 
                                @endif

                            @endforeach
                        </p>
                        @endif	

                        @if ($record->actors->count())
                        <p class="actors">
                            <span class="intro">Actores: </span>
                            @foreach($record->actors as $actor)
                                @if ($loop->index < 4)
                                    <a href="#">{{$actor->name}}</a>, 
                                @elseif ($loop->index == 4)
                                    <span class="more">más...</span>
                                    <a class="hide" href="#">{{$actor->name}}, </a> 
                                @else
                                    <a class="hide" href="#">{{$actor->name}}, </a>
                                @endif

                            @endforeach
                        </p>
                        @endif
                    </div>

                    <div class="ratings">
                    @if ($record->fa_rat)
                        <div class="ratings-provider">
                            <div class="ratings-provider-score">
                                <p>Filmaffinity</p>
                                <span>{{$record->fa_rat}}</span>
                            </div>
                            <div class="ratings-provider-count">
                                <div class="progress-bar"><span></span></div>
                                <span>{{$record->fa_count}}<span class="icon-man"></span></span>
                            </div>
                        </div>
                    @endif
                    @if ($record->im_rat)
                        <div class="ratings-provider">
                            <div class="ratings-provider-score">
                                <p>Filmaffinity</p>
                                <span>{{$record->im_rat}}</span>
                            </div>
                            <div class="ratings-provider-count">
                                <div class="progress-bar"><span></span></div>
                                <span>{{$record->im_count}}<span class="icon-man"></span></span>
                            </div>
                        </div>
                    @endif
                    </div>

                    <div class="cast">
                    @if ($record->netflix)
                        <ul class="slot">
                            <li>{{ $record->title }}</li>
                            <li>Netflix</li>
                            <li>@if ($record->netflix->online) <span class="icon-checkmark"></span> @endif</li>
                            @if ($record->netflix->new) <li>{{ $record->netflix->new->formatLocalized("%A %d %B %Y") }}</li> @endif
                            @if ($record->netflix->expire) <li>{{ $record->netflix->expire->formatLocalized("%A %d %B %Y") }}</li> @endif
                        </ul>
                    @endif
                    @if ($record->amazon)
                        <ul class="slot">
                            <li>{{ $record->title }}</li>
                            <li>Amazon</li>
                            <li>@if ($record->amazon->online) <span class="icon-checkmark"></span> @endif</li>
                        </ul>
                    @endif
                    @if ($record->hbo)
                        <ul class="slot">
                            <li>{{ $record->title }}</li>
                            <li>Hbo</li>
                            <li>@if ($record->hbo->online) <span class="icon-checkmark"></span> @endif</li>
                        </ul>
                    @endif
                    </div>
                </div>
            </article>
        </div>



    </div>
                    @if ($record->fa_rat)
                        <li class="score">
                            <span class="card-provider card-provider-fa">Filmaffinity</span>
                            <span class="score-value">{!! $record->faRatFormat!!}</span>
                        </li>
                        @endif
                        @if ($record->im_rat)
                        <li class="score">
                            <span class="card-provider card-provider-im">IMDb</span>
                            <span class="score-value">{!! $record->imRatFormat!!}</span>
                        </li>
                        @endif

                    @if ($record->im_rat)
                        <ul class="rating">
                            <li class="source"><p>IMDB</p></li>
                            <li class="stars"><div class="rat">{{ $record->im_rat }}</div>{!! $record->im_stars !!}</li>
                            <li>
                                <ul class="popularity-list">
                                    <li class="popularity-tag">POPULAR</li>
                                    <li class="popularity popularity-{{ $record->im_popularity['class'] }}"><span class="popularity-inner"></span></li>
                                    <li class="count">{{ $record->im_count }}</li>
                                </ul>
                            </li>
                        </ul>
                    @endif
                    @if ($record->rt_rat)
                        <ul class="rating">
                            <li class="source"><p>Rotten Tomattoes</p></li>
                            <li class="stars"><div class="rat">{{ $record->rt_rat }}</div>{!! $record->rt_stars !!}</li>
                            <li>
                                <ul class="popularity-list">
                                    <li class="popularity-tag">POPULAR</li>
                                    <li class="popularity popularity-{{ $record->rt_popularity['class'] }}"><span class="popularity-inner"></span></li>
                                    <li class="count">{{ $record->rt_count }}</li>
                                </ul>
                            </li>
                        </ul>
                    @endif
                </div>
                @if ($record->relationLoaded('movistarHistory'))
                    <div class="slots">
                        @foreach ($record->movistarTime as $slot)
                            <div>Emisiones</div>
                            <ul class="slot">
                                <li>{{ $record->title }}</li>
                                <li>Emisión de Televisión</li>
                                <li><span class="icon-tv"></span> {{ $slot->channel }}</li>
                                <li><span class="icon-clock"></span> {{ $slot->time->formatLocalized("%A %d %B %Y") }}</li>
                            </ul>
                        @endforeach
                        @foreach ($record->movistarHistory as $slot)
                            <div>Emisiones</div>
                            <ul class="slot slot-expire">
                                <li>{{ $record->title }}</li>
                                <li>Emisión de Televisión</li>
                                <li><span class="icon-tv"></span> {{ $slot->channel }}</li>
                                <li><span class="icon-clock"></span> {{ $slot->time->formatLocalized("%A %d %B %Y") }}</li>
                            </ul>
                        @endforeach
                    </div>
                @endif
            </article>
		</div>

	</div>

@endsection