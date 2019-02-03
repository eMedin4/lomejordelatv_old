<div class="sidebar">
    <div class="wrapper">
        <div class="ad"><span>lomejordelatv</span></div>
        
        <ul class="filters-nav">
            @if (Route::currentRouteName() == 'tv')
                <!-- <li><span class="nav-title">Contenido</span></li> -->
                <li><a href="{{route('tv', ['type' => 'peliculas', 'channel' => $channel])}}" class="{{($routeInfo['type'] == 'peliculas') ? 'active' : ''}}">Películas de Tv</a></li>
                <li><a href="{{route('tv', ['type' => 'series', 'channel' => $channel])}}" class="{{($routeInfo['type'] == 'series') ? 'active' : ''}}">Series de Tv</a></li>
                <li class="break"></li>
                <!-- <li><span class="nav-title">Por fecha</span></li> -->
                <li><a href="{{route('tv', ['type' => $type, 'channel' => $channel, 'time' => 'todas', 'sort' => $sort])}}" class="{{($routeInfo['time'] == 'cualquier-momento') ? 'active' : ''}}">Todas</a></li>
                <li><a href="{{route('tv', ['type' => $type, 'channel' => $channel, 'time' => 'hoy', 'sort' => $sort])}}" class="{{($routeInfo['time'] == 'ahora') ? 'active' : ''}}">Hoy</a></li>
                <li><a href="{{route('tv', ['type' => $type, 'channel' => $channel, 'time' => 'ahora', 'sort' => $sort])}}" class="{{($routeInfo['time'] == 'hoy') ? 'active' : ''}}">Ahora</a></li>
                <li><a href="{{route('tv', ['type' => $type, 'channel' => $channel, 'time' => 'esta-noche', 'sort' => $sort])}}" class="{{($routeInfo['time'] == 'esta-noche') ? 'active' : ''}}">Esta noche</a></li>
                <li><a href="{{route('tv', ['type' => $type, 'channel' => $channel, 'time' => 'manana', 'sort' => $sort])}}" class="{{($routeInfo['time'] == 'manana') ? 'active' : ''}}">Mañana</a></li>
                <li class="break"></li>
                <!-- <li><span class="nav-title">Ordenar por</span></li> -->
                <li><a href="{{route('tv', ['type' => $type, 'channel' => $channel, 'time' => $time, 'sort' => 'destacadas'])}}" class="{{($routeInfo['sort'] == 'destacadas') ? 'active' : ''}}">Destacadas</a></li>
                <li><a href="{{route('tv', ['type' => $type, 'channel' => $channel, 'time' => $time, 'sort' => 'populares'])}}" class="{{($routeInfo['sort'] == 'populares') ? 'active' : ''}}">Populares</a></li>
                <li><a href="{{route('tv', ['type' => $type, 'channel' => $channel, 'time' => $time, 'sort' => 'mejores'])}}" class="{{($routeInfo['sort'] == 'mejores') ? 'active' : ''}}">Mejor puntuación</a></li>
                <li class="break"></li>
                <!-- <li><span class="nav-title">Canales</span></li> -->
                <li><a href="{{route('tv', ['type' => $type, 'channel' => 'tv', 'time' => $time, 'sort' => $sort])}}" class="">Todos</a></li>
                <li><a href="{{route('tv', ['type' => $type, 'channel' => 'tdt', 'time' => $time, 'sort' => $sort])}}" class="">Canales Tdt</a></li>
                <li><a href="{{route('tv', ['type' => $type, 'channel' => 'canales-de-pago', 'time' => $time, 'sort' => $sort])}}" class="">Canales de pago</a></li>



            @elseif (Route::currentRouteName() == 'netflix')

                <li><a class="{{($routeInfo['type'] == 'peliculas') ? 'active' : ''}}" href="{{route('netflix', ['type' => 'peliculas', 'list' => $routeInfo['list']])}}">Películas</a></li>
                <li><a class="{{($routeInfo['type'] == 'series') ? 'active' : ''}}" href="{{route('netflix', ['type' => 'series', 'list' => $routeInfo['list']])}}">Series</a></li>
                <li class="break"></li>
                <li><a class="{{($routeInfo['list'] == '') ? 'active' : ''}}" href="{{route('netflix', ['type' => $routeInfo['type']])}}">Recomendadas</a></li>
                <li><a class="{{($routeInfo['list'] == 'trending') ? 'active' : ''}}" href="{{route('netflix', ['type' => $routeInfo['type'], 'list' => 'trending'])}}">Trending</a></li>
                <li><a class="{{($routeInfo['list'] == 'nuevas') ? 'active' : ''}}" href="{{route('netflix', ['type' => $routeInfo['type'], 'list' => 'nuevas'])}}">Nuevas</a></li>
                <li><a class="{{($routeInfo['list'] == 'expiran') ? 'active' : ''}}" href="{{route('netflix', ['type' => $routeInfo['type'], 'list' => 'expiran'])}}">Que expiran</a></li>
                <li><a class="{{($routeInfo['list'] == 'mejores') ? 'active' : ''}}" href="{{route('netflix', ['type' => $routeInfo['type'], 'list' => 'mejores'])}}">Por nota</a></li>
                <li><a class="{{($routeInfo['list'] == 'populares') ? 'active' : ''}}" href="{{route('netflix', ['type' => $routeInfo['type'], 'list' => 'populares'])}}">Por popularidad</a></li>

            @elseif (Route::currentRouteName() == 'amazon')

                <li><a class="{{($routeInfo['type'] == 'peliculas') ? 'active' : ''}}" href="{{route('amazon', ['type' => 'peliculas', 'list' => $routeInfo['list']])}}">Películas</a></li>
                <li><a class="{{($routeInfo['type'] == 'series') ? 'active' : ''}}" href="{{route('amazon', ['type' => 'series', 'list' => $routeInfo['list']])}}">Series</a></li>
                <li class="break"></li>
                <li><a class="{{($routeInfo['list'] == '') ? 'active' : ''}}" href="{{route('amazon', ['type' => $routeInfo['type']])}}">Recomendadas</a></li>
                <li><a class="{{($routeInfo['list'] == 'trending') ? 'active' : ''}}" href="{{route('amazon', ['type' => $routeInfo['type'], 'list' => 'trending'])}}">Trending</a></li>
                <li><a class="{{($routeInfo['list'] == 'mejores') ? 'active' : ''}}" href="{{route('amazon', ['type' => $routeInfo['type'], 'list' => 'mejores'])}}">Por nota</a></li>
                <li><a class="{{($routeInfo['list'] == 'populares') ? 'active' : ''}}" href="{{route('amazon', ['type' => $routeInfo['type'], 'list' => 'populares'])}}">Por popularidad</a></li>

            @elseif (Route::currentRouteName() == 'hbo')

                <li><a class="{{($routeInfo['type'] == 'peliculas') ? 'active' : ''}}" href="{{route('hbo', ['type' => 'peliculas', 'list' => $routeInfo['list']])}}">Películas</a></li>
                <li><a class="{{($routeInfo['type'] == 'series') ? 'active' : ''}}" href="{{route('hbo', ['type' => 'series', 'list' => $routeInfo['list']])}}">Series</a></li>
                <li class="break"></li>
                <li><a class="{{($routeInfo['list'] == '') ? 'active' : ''}}" href="{{route('hbo', ['type' => $routeInfo['type']])}}">Recomendadas</a></li>
                <li><a class="{{($routeInfo['list'] == 'trending') ? 'active' : ''}}" href="{{route('hbo', ['type' => $routeInfo['type'], 'list' => 'trending'])}}">Trending</a></li>
                <li><a class="{{($routeInfo['list'] == 'mejores') ? 'active' : ''}}" href="{{route('hbo', ['type' => $routeInfo['type'], 'list' => 'mejores'])}}">Por nota</a></li>
                <li><a class="{{($routeInfo['list'] == 'populares') ? 'active' : ''}}" href="{{route('hbo', ['type' => $routeInfo['type'], 'list' => 'populares'])}}">Por popularidad</a></li>

            @endif
        </ul>
    </div>
</div>


    


    

    


    

    {{--<!--
    <span class="break first">Contenido</span>

    <a href="{{route('peliculas-de-netflix', ['type' => ''])}}" class="type-link active">Películas de Netflix</a>
    <a href="{{route('weries-de-tv', ['type' => ''])}}" class="type-link">Series de Netflix</a>

    <span class="break">Estado temporal</span>

    <a href="{{route('series-tv', ['type' => ''])}}" class="type-link active">Todas</a>
    <a href="{{route('peliculas-tv', ['type' => ''])}}" class="type-link">Nuevas</a>
    <a href="{{route('peliculas-tv', ['type' => ''])}}" class="type-link">Que expiran</a>

    <span class="break">Ordenar por</span>

    <a href="{{route('peliculas-tv', ['type' => ''])}}" class="type-link active">Destacadas</a>
    <a href="{{route('peliculas-tv', ['type' => ''])}}" class="type-link">Más populares</a>
    <a href="{{route('peliculas-tv', ['type' => ''])}}" class="type-link">Mejor puntuación</a>
    -->--}}



