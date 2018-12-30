<div class="ad"><span>lomejordelatv</span></div>

{{--dd($parameters, $type, $channel, $time, $sort, Route::current()->parameters())--}}
    <?php $sort = ($sort == 'destacadas') ? null : $sort; ?>
    <ul class="nav-filters">

        <li>
            <span class="nav-title">Contenido</span>
        </li>
        <li>
            <img src="images/021-clapperboard.svg">
            <a href="{{route('tv', ['type' => 'peliculas', 'channel' => $channel])}}" class="{{($parameters['type'] == 'peliculas') ? 'active' : ''}}">Películas de Tv</a>
        </li>
        <li>
            <img src="images/041-television-2.svg">
            <a href="{{route('tv', ['type' => 'series', 'channel' => $channel])}}" class="{{($parameters['type'] == 'series') ? 'active' : ''}}">Series de Tv</a>
        </li>
        <li>
            <span class="nav-title">Por fecha</span>
        </li>
        <li>
            <img src="images/036-earth-grid.svg">
            <a href="{{route('tv', ['type' => $type, 'channel' => $channel, 'time' => 'todas', 'sort' => $sort])}}" class="{{($parameters['time'] == 'cualquier-momento') ? 'active' : ''}}">Todas</a>
        </li>
        <li>
            <img src="images/038-wall-clock.svg">
            <a href="{{route('tv', ['type' => $type, 'channel' => $channel, 'time' => 'hoy', 'sort' => $sort])}}" class="{{($parameters['time'] == 'ahora') ? 'active' : ''}}">Hoy</a>
        </li>
        <li>
            <img src="images/035-calendar.svg">
            <a href="{{route('tv', ['type' => $type, 'channel' => $channel, 'time' => 'ahora', 'sort' => $sort])}}" class="{{($parameters['time'] == 'hoy') ? 'active' : ''}}">Ahora</a>
        </li>
        <li>
            <img src="images/night.svg">
            <a href="{{route('tv', ['type' => $type, 'channel' => $channel, 'time' => 'esta-noche', 'sort' => $sort])}}" class="{{($parameters['time'] == 'esta-noche') ? 'active' : ''}}">Esta noche</a>
        </li>
        <li>
            <img src="images/calendar.svg">
            <a href="{{route('tv', ['type' => $type, 'channel' => $channel, 'time' => 'manana', 'sort' => $sort])}}" class="{{($parameters['time'] == 'manana') ? 'active' : ''}}">Mañana</a>
        </li>
        <li>
            <span class="nav-title">Ordenar por</span>
        </li>
        <li>
            <img src="images/016-like.svg">
            <a href="{{route('tv', ['type' => $type, 'channel' => $channel, 'time' => $time, 'sort' => 'destacadas'])}}" class="{{($parameters['sort'] == 'destacadas') ? 'active' : ''}}">Destacadas</a>
        </li>
        <li>
            <img src="images/038-like-1.svg">
            <a href="{{route('tv', ['type' => $type, 'channel' => $channel, 'time' => $time, 'sort' => 'populares'])}}" class="{{($parameters['sort'] == 'populares') ? 'active' : ''}}">Populares</a>
        </li>
        <li>
            <img src="images/favorites.svg">
            <a href="{{route('tv', ['type' => $type, 'channel' => $channel, 'time' => $time, 'sort' => 'mejores'])}}" class="{{($parameters['sort'] == 'mejores') ? 'active' : ''}}">Mejor puntuación</a>
        </li>
        <li>
            <span class="nav-title">Canales</span>
        </li>
        <li>
            <img src="images/046-tablet.svg">
            <a href="{{route('tv', ['type' => $type, 'channel' => 'tv', 'time' => $time, 'sort' => $sort])}}" class="">Todos</a>
        </li>
        <li>
            <img src="images/017-remote-control.svg">
            <a href="{{route('tv', ['type' => $type, 'channel' => 'tdt', 'time' => $time, 'sort' => $sort])}}" class="">Canales Tdt</a>
        </li>
        <li>
            <img src="images/047-television-1.svg">
            <a href="{{route('tv', ['type' => $type, 'channel' => 'canales-de-pago', 'time' => $time, 'sort' => $sort])}}" class="">Canales de pago</a>
        </li>
    </ul>


    


    

    


    

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



