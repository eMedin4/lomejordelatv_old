{{--Cada record es una colección--}}
<div class="card-inner">
    <a href="{{ route('movie', $record->slug) }}">
        <div class="thumb">
            @if ($thumb)
                <img src="https://s3.eu-west-3.amazonaws.com/lomejordelatv/movieimages/backgrounds/{{ $thumb }}/{{ $record->slug }}.jpg" alt="">
            @endif
            <ul class="meta">
                <li class="meta-details"><span class="country country-{{ str_slug($record->country) }}"></span>{{ $record->year }}</li>
                @if ($record->score)
                    <li class="stars">{!! $record->fa_stars !!}</li>
                    <li class="score"><span class="icon-star-full"></span><span class="score-rank">{{ $record->score}}<i>/10</i></span></li>
                @endif

                @if ($record->hot) <li class="tag tag-hot">HOT</li> @endif
                
                @if ($record->new) <li class="tag tag-new">NEW</li> @endif
            </ul>
        </div>
        <{{ $heading }}>{{ $record->title }}</{{ $heading }}>
    </a>
    
    <section class="info">
        
        @if ($excerpt)
            <p class="excerpt">{{ $record->{$excerpt} }}</p>
        @endif

        @foreach ($recordsCollection as $program)
            <div class="ref">
                <div class="program">
                    <div class="channel"> {{ $program->channel }},</div>
                    <time> {!! $program->format_time !!}</time>
                </div>
                @if ($program->season && $program->episode)
                    <div class="season">
                        <span>T.</span>{{$program->season}}
                        <span>E.</span>{{$program->episode}}
                    </div>
                @endif
            </div>
        @endforeach

        @include('includes.develop-data')

    </section>
</div>
