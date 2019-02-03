@extends('administration.layout')

@section('content')
<form method="GET" action="{{route('administration.manageMovies', ['provider' => $provider])}}" data-url="{{route('administration.postManageMovies')}}">
    @csrf
    @if (isset($responseMessage))
        <div class="message"><span>{{$responseMessage}}</span></div>
    @endif
    <div class="content-bar">
        <ul class="content-bar-menu">
            <li>
                @if ($provider == 'netflix') <span>Netflix</span> @else <a href="{{route('administration.manageMovies', ['provider' => 'netflix'])}}">Netflix</a> @endif
            </li>
            <li>
                @if ($provider == 'amazon') <span>Amazon</span> @else <a href="{{route('administration.manageMovies', ['provider' => 'amazon'])}}">Amazon</a> @endif
            </li>
            <li>
                @if ($provider == 'hbo') <span>Hbo</span> @else <a href="{{route('administration.manageMovies', ['provider' => 'hbo'])}}">Hbo</a> @endif
            </li>
        </ul>
        <select name="sort">
            <option value="destacadas" selected="selected">destacadas</option>
            <option value="trending">trending</option>
            <option value="votos">más votos</option>
            <option value="nota">más nota</option>
        </select>
        <button type="submit" name="search">Buscar</button>
    </div>
    <div class="content content-testing">

        <div class="card-wrap card-wrap-50">
            <div class="card">
                <h3>{{$provider}} PELICULAS</h3>
                    <div class="table">
                        <div class="thead">
                            <div class="cell"></div>
                            {{-- <div class="cell">rank</div> --}}
                            <div class="cell">título</div>
                            <div class="cell">año</div>
                            <div class="cell">mov pop</div>
                            <div class="cell">prov trend</div>
                            <div class="cell">hot</div>
                            <div class="cell">new</div>
                            <div class="cell">expire</div>
                        </div>
                        
                        @foreach ($movies as $item)
                            <div class="row">
                                <div class="cell">{{$loop->iteration}}</div>
                                {{-- <div class="cell">{{$item->rank}}</div> --}}
                                <div class="cell">{{$item->movie->title}}</div>
                                <div class="cell">{{$item->movie->year}}</div>
                                <div class="cell">{{$item->movie->popularity}}</div>
                                <div class="cell">{{$item->trend}}</div>
                                <div class="cell cell-hot">
                                    <span class="hot hot-{{$item->hot}}" data-id="{{$item->id}}" data-value="{{$item->hot}}">.</span>
                                    <span class="hot-up">+</span>
                                    <span class="hot-down">-</span>
                                </div>
                                <div class="cell">@if ($item->new) {{$item->new->format('d/m/y')}} @endif</div>
                                <div class="cell">@if ($item->expire) {{$item->expire->format('d/m/y')}} @endif</div>

                            </div>
                        @endforeach
                    </div>
            </div>
        </div>

        <div class="card-wrap card-wrap-50">
            <div class="card">
                <h3>{{$provider}} SERIES</h3>
                    <div class="table">
                        <div class="thead">
                            <div class="cell"></div>
                            <div class="cell">título</div>
                            <div class="cell">año</div>
                            <div class="cell">mov pop</div>
                            <div class="cell">prov trend</div>
                            <div class="cell">hot</div>
                            <div class="cell">new</div>
                            <div class="cell">expire</div>
                        </div>
                        @foreach ($shows as $item)
                            <div class="row">
                                <div class="cell">{{$loop->iteration}}</div>
                                <div class="cell">{{$item->movie->title}}</div>
                                <div class="cell">{{$item->movie->year}}</div>
                                <div class="cell">{{$item->movie->popularity}}</div>
                                <div class="cell">{{$item->trend}}</div>
                                <div class="cell cell-hot">
                                    <span class="hot hot-{{$item->hot}}">.</span>
                                    <span class="hot-up">+</span>
                                    <span class="hot-down">-</span>
                                </div>
                                <div class="cell">@if ($item->new) {{$item->new->format('d/m/y')}} @endif</div>
                                <div class="cell">@if ($item->expire) {{$item->expire->format('d/m/y')}} @endif</div>
                            </div>
                        @endforeach
                    </div>
            </div>
        </div>

    </div>
</form>
@endsection