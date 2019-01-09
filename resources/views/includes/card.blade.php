{{--Cada record es una colección--}}
<div class="card">
    <a href="{{ route('movie', $record->slug) }}">
        <div class="card-meta">
            @if ($record->check_background)
 
                <div class="thumb">
                    <img src="https://s3.eu-west-3.amazonaws.com/lomejordelatv/movieimages/backgrounds/{{ ($loop->index == 1 || $loop->index == 6) ? 'lrg' : 'sml' }}/{{ $record->slug }}.jpg" alt="">
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

        <div class="card-title">
            @if ($loop->index == 1 || $loop->index == 6)
                <p>{{ $record->title }}</p>
            @else
                <p>{{ $record->title }}</p>
            @endif
        </div>
    </a>

    <div class="card-extra">

        <ul class="card-tags">
            <li class="card-tags-details">
                <span class="country country-{{ str_slug($record->country) }}"></span>
                {{ $record->year }}
            </li>
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
            @if ($record->hot)
            <li class="tag hot">
                HOT
            </li> 
            @endif
            @if ($record->new) 
            <li class="tag new">
                NEW
            </li> 
            @endif
        </ul>
        
        {{--
        @if ($excerpt)
            <p class="card-excerpt">{{ ($loop->index == 1 || $loop->index == 6) ? $record->excerpt400 : $record->excerpt200 }}</p>
        @endif
        --}}
        
        @if ($routeInfo['channel'] == 'tv')

            <div class="card-program">
                @foreach ($recordCollection as $program)
                    @if ($loop->index == 2) <div class="dropdown"> @endif
                        <div class="card-program-item">
                            <div class="card-program-item-main">
                                <time> {!! $program->format_time !!}</time>
                                <span> {{ $program->channel }} </span>
                            </div>
                            @if ($program->season && $program->episode)
                            <div class="card-program-item-season">
                                <span>T.</span>{{$program->season}}
                                <span>E.</span>{{$program->episode}}
                            </div>
                            @endif
                        </div>
                    @if (($loop->index >= 2) && $loop->last) 
                        </div> 
                        <div class="dropdown-btn"><span>+ pases</span></div>
                    @endif
                @endforeach
            </div>

        @elseif ($routeInfo['channel'] == 'netflix' && $routeInfo['type'] == 'series')

            <div class="card-program">
                @foreach ($record->providersseasons as $season)
                    <div class="card-season">T {{$season->number}}</div>
                @endforeach
            </div>

        @endif


    </div>
</div>
