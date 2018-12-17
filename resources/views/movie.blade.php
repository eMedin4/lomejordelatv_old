@extends('layouts.layout')

@section('content')

	<div class="movie-layout">

		<div class="col-1">
            <div class="movie-aside-card">
                <img src="https://s3.eu-west-3.amazonaws.com/lomejordelatv/movieimages/posters/lrg/{{ $record->slug }}.jpg" alt="">
            </div>
		</div>

		<div class="col-2">
            <article class="movie-main-card">
                <div class="thumb">
                    <img src="https://s3.eu-west-3.amazonaws.com/lomejordelatv/movieimages/backgrounds/lrg/{{ $record->slug }}.jpg" alt="">
                    <div class="darker"></div>
                </div>
                <div class="info">
                    <h1 class="h1">{{ $record->title }}</h1>
                    <ul class="meta">
                        <li class="country country-{{ $record->country }}"></li>
                        <li class="year">{{ $record->year }}</li>
						<li class="break"></li>
                        <li>{{ $record->original_title }}</li>
                        <li class="break"></li>
                        <li>{{ $record->duration }}</li>
                        <li class="break"></li>
                        <li>
                            @foreach ($record->genres as $genre)
                                {{ $loop->first ? '' : ', ' }}
                                <span>{{ $genre->name }}</span>
                            @endforeach
                        </li>
                    </ul>
                    <p>{{ $record->review }}</p>
                </div>
                <div class="ratings">
                    @if ($record->fa_rat)
                        <ul class="rating">
                            <li class="source"><p>Filmaffinity</p></li>
                            <li class="stars"><div class="rat">{{ $record->fa_rat }}</div>{!! $record->fa_stars !!}</li>
                            <li>
                                <ul class="popularity-list">
                                    <li class="popularity-tag">POPULAR</li>
                                    <li class="popularity popularity-{{ $record->fa_popularity["class"] }}"><span class="popularity-inner"></span></li>
                                    <li class="count">{{ $record->fa_count }}</li>
                                </ul>
                            </li>
                        </ul>
                    @endif
                    @if ($record->im_rat)
                        <ul class="rating">
                            <li class="source"><p>IMDB</p></li>
                            <li class="stars"><div class="rat">{{ $record->im_rat }}</div>{!! $record->im_stars !!}</li>
                            <li>
                                <ul class="popularity-list">
                                    <li class="popularity-tag">POPULAR</li>
                                    <li class="popularity popularity-{{ $record->im_popularity["class"] }}"><span class="popularity-inner"></span></li>
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
                                    <li class="popularity popularity-{{ $record->rt_popularity["class"] }}"><span class="popularity-inner"></span></li>
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