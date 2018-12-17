<div class="card-inner">
    <a href="{{ route('movie', $record->movie->slug) }}">
        @if ($thumb)
            <img src="https://s3.eu-west-3.amazonaws.com/lomejordelatv/movieimages/backgrounds/sml/{{ $record->movie->slug }}.jpg" alt="">
        @endif
        <ul class="meta">
            <li class="country country-{{ str_slug($record->movie->country) }}"></li>
            <li class="year">{{ $record->movie->year }}</li>
            <li class="stars">{!! $record->movie->fa_stars !!}</li>
            @if ($record->movie->score)
                <li class="score"><span class="icon-star-full"><span class="score-rank">{{ $record->movie->score}}/10</span></li>
            @endif
            @if ($record->movie->fa_popularity_class > 2) <li class="popularity-tag-hot">top</li>
            @elseif ($record->movie->fa_popularity_class = 2) <li class="popularity-tag-warm">HOT</li>
            @endif
        </ul>
        <{{ $heading }}>{{ $record->movie->title }}</{{ $heading }}>
    </a>
    
    <section class="info">
        
        
        @if ($excerpt)
            <p class="excerpt">{{ $record->movie->excerpt200 }}</p>
        @endif

        <div class="program">
            <div class="channel"> {{ $record->channel }},</div>
            <time> {!! $record->format_time !!}</time>
        </div>

        @include('includes.develop-data')

    </section>
</div>
