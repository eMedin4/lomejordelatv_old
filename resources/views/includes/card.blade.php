{{--Cada record es una colección--}}
<div class="card">
    <a href="{{ route('movie', $record->movie->slug) }}">
        <div class="card-meta">
            @if ($record->movie->check_background)
 
                <div class="thumb">
                    <img src="https://s3.eu-west-3.amazonaws.com/lomejordelatv/movieimages/backgrounds/{{ ($loop->index == 0 ) ? 'lrg' : 'sml' }}/{{ $record->movie->slug }}.jpg" alt="">
                    @if ($record->hot)
                        <div class="card-hot">
                            <span>Trending</span>
                        </div>
                    @endif
                </div>

                @if ($record->movie->check_poster)
                    <div class="poster">
                        <img src="https://s3.eu-west-3.amazonaws.com/lomejordelatv/movieimages/posters/lrg/{{ $record->movie->slug }}.jpg" alt="">
                        
                        @if ($record->new) 
                            <div class="card-new">
                                <span>Nuevo en</span>
                                <i class="netflix-logo">NETFLIX</i>
                            </div>
                        @endif 
                    </div>
                @else
                    <div class="no-poster"></div>
                @endif

            @else
                <div class="no-thumb">

                    @if ($record->movie->check_poster)
                        <div class="poster">
                            <img src="https://s3.eu-west-3.amazonaws.com/lomejordelatv/movieimages/posters/lrg/{{ $record->movie->slug }}.jpg" alt="">
                        </div>
                    @else
                        <div class="no-poster"></div>
                    @endif

                </div>
            @endif

        </div>

        <div class="card-title"><p>{{ $record->movie->title }}</p></div>
    </a>

    <div class="card-extra">

        <ul class="card-tags">
            <li class="card-tags-details">
                <span class="country country-{{ str_slug($record->movie->country) }}"></span>
                {{ $record->movie->year }}
            </li>
            @if ($record->movie->fa_rat)
            <li class="score break">
                <span class="card-provider card-provider-fa">Filmaffinity</span>
                <span class="score-value">{{ $record->movie->fa_rat }} <i>{!! $record->movie->formatFaCount !!}</i></span>
            </li>
            @endif
            @if ($record->movie->im_rat)
            <li class="score break">
                <span class="card-provider card-provider-im">IMDb</span>
                <span class="score-value">{{ $record->movie->im_rat }} <i>{!! $record->movie->formatImCount !!}</i></span>
            </li>
            @endif
            
        </ul>
        
        {{--
        @if ($excerpt)
            <p class="card-excerpt">{{ ($loop->index == 1 || $loop->index == 6) ? $record->movie->excerpt400 : $record->movie->excerpt200 }}</p>
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

        @elseif ($record->providersseasons()->exists())

        <div class="card-program">
            @foreach ($record->providersseasons as $season)
                <div class="card-season">t.{{$season->number}}</div>
            @endforeach
        </div>

        @endif


        


    </div>
</div>
