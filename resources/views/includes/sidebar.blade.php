<div class="ad"><span>lomejordelatv</span></div>

{{--dd($parameters, $type, $channel, $time, $sort, Route::current()->parameters())--}}

    <span class="break first">Contenido</span>

    <a href="{{route('tv', ['type' => 'peliculas', 'channel' => $channel])}}" class="type-link {{($parameters['type'] == 'peliculas') ? 'active' : ''}}">Películas de Tv</a>
    <a href="{{route('tv', ['type' => 'series', 'channel' => $channel])}}" class="type-link {{($parameters['type'] == 'series') ? 'active' : ''}}">Series de Tv</a>

    <span class="break">Por fecha</span>

    <?php $sort = ($sort == 'destacadas') ? null : $sort; ?>
    <a href="{{route('tv', ['type' => $type, 'channel' => $channel, 'time' => 'todas', 'sort' => $sort])}}" class="type-link {{($parameters['time'] == 'cualquier-momento') ? 'active' : ''}}">Todas</a>
    <a href="{{route('tv', ['type' => $type, 'channel' => $channel, 'time' => 'ahora', 'sort' => $sort])}}" class="type-link {{($parameters['time'] == 'hoy') ? 'active' : ''}}">Ahora</a>
    <a href="{{route('tv', ['type' => $type, 'channel' => $channel, 'time' => 'hoy', 'sort' => $sort])}}" class="type-link {{($parameters['time'] == 'ahora') ? 'active' : ''}}">Hoy</a>
    <a href="{{route('tv', ['type' => $type, 'channel' => $channel, 'time' => 'esta-noche', 'sort' => $sort])}}" class="type-link {{($parameters['time'] == 'esta-noche') ? 'active' : ''}}">Esta noche</a>
    <a href="{{route('tv', ['type' => $type, 'channel' => $channel, 'time' => 'manana', 'sort' => $sort])}}" class="type-link {{($parameters['time'] == 'manana') ? 'active' : ''}}">Mañana</a>

    <span class="break">Ordenar por</span>

    <a href="{{route('tv', ['type' => $type, 'channel' => $channel, 'time' => $time, 'sort' => 'destacadas'])}}" class="type-link active">Destacadas</a>
    <a href="{{route('tv', ['type' => $type, 'channel' => $channel, 'time' => $time, 'sort' => 'populares'])}}" class="type-link">Más populares</a>
    <a href="{{route('tv', ['type' => $type, 'channel' => $channel, 'time' => $time, 'sort' => 'mejores'])}}" class="type-link">Mejor puntuación</a>

    <span class="break">Canales</span>

    <a href="{{route('tv', ['type' => $type, 'channel' => 'tv', 'time' => $time, 'sort' => $sort])}}" class="type-link active">Todos</a>
    <a href="{{route('tv', ['type' => $type, 'channel' => 'tdt', 'time' => $time, 'sort' => $sort])}}" class="type-link">Canales Tdt</a>
    <a href="{{route('tv', ['type' => $type, 'channel' => 'canales-de-pago', 'time' => $time, 'sort' => $sort])}}" class="type-link">Canales de pago</a>
    

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



