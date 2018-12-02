<div class="card-inner">
    <a href="{{ route('movie', $record->movie->slug) }}">
        @if ($thumb)
            <img src="https://s3.eu-west-3.amazonaws.com/lomejordelatv/movieimages/backgrounds/sml/{{ $record->movie->slug }}.jpg" alt="">
        @endif
        <{{ $heading }}>{{ $record->movie->title }}</{{ $heading }}>
    </a>
    
    <section class="info">
        
        <ul class="meta">
            <li class="country country-{{ str_slug($record->movie->country) }}"></li>
            <li class="year">{{ $record->movie->year }}</li>
            <li class="stars">{!! $record->movie->fa_stars !!}</li>
            <li class="popularity-tag">popular</li>
            <li class="popularity popularity-{{ $record->movie->fa_popularity_class }}"><span class="popularity-inner"></span></li>
        </ul>
        @if ($excerpt)
            <p class="excerpt">{{ $record->movie->excerpt200 }}</p>
        @endif

        <div class="program">
            <div class="channel"><span class="icon-tv"></span> {{ $record->channel }}</div>
            <time><span class="icon-clock"></span> {!! $record->format_time !!}</time>
        </div>

        {{-- @include('includes.develop-data') --}}

    </section>
</div>