<div class="ad"><span>lomejordelatv</span></div>

{{--dd($parameters, $type, $channel, $time, $sort, Route::current()->parameters())--}}
    <?php $sort = ($sort == 'destacadas') ? null : $sort; ?>
    <ul class="filters-nav">
        @if (Route::currentRouteName() == 'tv')
            <li><span class="nav-title">Contenido</span></li>
            <li><a href="{{route('tv', ['type' => 'peliculas', 'channel' => $channel])}}" class="{{($routeInfo['type'] == 'peliculas') ? 'active' : ''}}">Películas de Tv</a></li>
            <li><a href="{{route('tv', ['type' => 'series', 'channel' => $channel])}}" class="{{($routeInfo['type'] == 'series') ? 'active' : ''}}">Series de Tv</a></li>
            <li><span class="nav-title">Por fecha</span></li>
            <li><a href="{{route('tv', ['type' => $type, 'channel' => $channel, 'time' => 'todas', 'sort' => $sort])}}" class="{{($routeInfo['time'] == 'cualquier-momento') ? 'active' : ''}}">Todas</a></li>
            <li><a href="{{route('tv', ['type' => $type, 'channel' => $channel, 'time' => 'hoy', 'sort' => $sort])}}" class="{{($routeInfo['time'] == 'ahora') ? 'active' : ''}}">Hoy</a></li>
            <li><a href="{{route('tv', ['type' => $type, 'channel' => $channel, 'time' => 'ahora', 'sort' => $sort])}}" class="{{($routeInfo['time'] == 'hoy') ? 'active' : ''}}">Ahora</a></li>
            <li><a href="{{route('tv', ['type' => $type, 'channel' => $channel, 'time' => 'esta-noche', 'sort' => $sort])}}" class="{{($routeInfo['time'] == 'esta-noche') ? 'active' : ''}}">Esta noche</a></li>
            <li><a href="{{route('tv', ['type' => $type, 'channel' => $channel, 'time' => 'manana', 'sort' => $sort])}}" class="{{($routeInfo['time'] == 'manana') ? 'active' : ''}}">Mañana</a></li>
            <li><span class="nav-title">Ordenar por</span></li>
            <li><a href="{{route('tv', ['type' => $type, 'channel' => $channel, 'time' => $time, 'sort' => 'destacadas'])}}" class="{{($routeInfo['sort'] == 'destacadas') ? 'active' : ''}}">Destacadas</a></li>
            <li><a href="{{route('tv', ['type' => $type, 'channel' => $channel, 'time' => $time, 'sort' => 'populares'])}}" class="{{($routeInfo['sort'] == 'populares') ? 'active' : ''}}">Populares</a></li>
            <li><a href="{{route('tv', ['type' => $type, 'channel' => $channel, 'time' => $time, 'sort' => 'mejores'])}}" class="{{($routeInfo['sort'] == 'mejores') ? 'active' : ''}}">Mejor puntuación</a></li>
            <li><span class="nav-title">Canales</span></li>
            <li><a href="{{route('tv', ['type' => $type, 'channel' => 'tv', 'time' => $time, 'sort' => $sort])}}" class="">Todos</a></li>
            <li><a href="{{route('tv', ['type' => $type, 'channel' => 'tdt', 'time' => $time, 'sort' => $sort])}}" class="">Canales Tdt</a></li>
            <li><a href="{{route('tv', ['type' => $type, 'channel' => 'canales-de-pago', 'time' => $time, 'sort' => $sort])}}" class="">Canales de pago</a></li>
        @elseif (Route::currentRouteName() == 'netflix')
            <li><span class="nav-title">Contenido</span></li>
            <li><a href="{{route('netflix', ['type' => 'peliculas'])}}" class="{{($routeInfo['type'] == 'peliculas') ? 'active' : ''}}">Películas de Tv</a></li>
            <li><a href="{{route('netflix', ['type' => 'series'])}}" class="{{($routeInfo['type'] == 'series') ? 'active' : ''}}">Series de Tv</a></li>
            <li><span class="nav-title">Estado</span></li>
            <li><a href="{{route('netflix', ['type' => $type, 'time' => 'todas', 'sort' => $sort])}}" class="{{($routeInfo['time'] == 'todas') ? 'active' : ''}}">Todas</a></li>
            <li><a href="{{route('netflix', ['type' => $type, 'time' => 'nuevas', 'sort' => $sort])}}" class="{{($routeInfo['time'] == 'nuevas') ? 'active' : ''}}">Nuevas</a></li>
            <li><a href="{{route('netflix', ['type' => $type, 'time' => 'expiran', 'sort' => $sort])}}" class="{{($routeInfo['time'] == 'expiran') ? 'active' : ''}}">Expiran</a></li>
            <li><span class="nav-title">Ordenar por</span></li>
            <li><a href="{{route('netflix', ['type' => $type, 'time' => $time, 'sort' => 'destacadas'])}}" class="{{($routeInfo['sort'] == 'destacadas') ? 'active' : ''}}">Destacadas</a></li>
            <li><a href="{{route('netflix', ['type' => $type, 'time' => $time, 'sort' => 'populares'])}}" class="{{($routeInfo['sort'] == 'populares') ? 'active' : ''}}">Populares</a></li>
            <li><a href="{{route('netflix', ['type' => $type, 'time' => $time, 'sort' => 'mejores'])}}" class="{{($routeInfo['sort'] == 'mejores') ? 'active' : ''}}">Mejor puntuación</a></li>
            <li><span class="nav-title">Año</span></li>
            {{--<!-- <li>
                <form class="filters-year-form @if ($fromYear && $toYear) active @endif" action="{{route('processFiltersYearForm', ['type' => $type, 'channel' => 'netflix', 'time' => $time, 'sort' => $sort])}}" method="post">
                    @csrf
                        <select name="from-year">
                        <option selected disabled>Desde</option>
                        @for ($i = 2019; $i > 1920; $i--)
                        if ($fromYear == $i)
                        <option value="{{$i}}" @if ($fromYear == $i) selected @endif>{{$i}}</option>
                        @endfor
                    </select>
                    <span class="break">-</span>
                    <select name="to-year">
                        <option selected disabled>Hasta</option>
                        @for ($i = 2019; $i > 1920; $i--)
                        <option value="{{$i}}" @if ($toYear == $i) selected @endif>{{$i}}</option>
                        @endfor
                    </select>
                    <button type="submit">>></button>
                </form>
            </li> -->--}}



        @elseif (Route::currentRouteName() == 'amazon')
            <li><span class="nav-title">Contenido</span></li>
            <li><a href="{{route('amazon', ['type' => 'peliculas'])}}" class="{{($routeInfo['type'] == 'peliculas') ? 'active' : ''}}">Películas de Tv</a></li>
            <li><a href="{{route('amazon', ['type' => 'series'])}}" class="{{($routeInfo['type'] == 'series') ? 'active' : ''}}">Series de Tv</a></li>
            <li><span class="nav-title">Ordenar por</span></li>
            <li><a href="{{route('amazon', ['type' => $type, 'sort' => 'destacadas'])}}" class="{{($routeInfo['sort'] == 'destacadas') ? 'active' : ''}}">Destacadas</a></li>
            <li><a href="{{route('amazon', ['type' => $type, 'sort' => 'populares'])}}" class="{{($routeInfo['sort'] == 'populares') ? 'active' : ''}}">Populares</a></li>
            <li><a href="{{route('amazon', ['type' => $type, 'sort' => 'mejores'])}}" class="{{($routeInfo['sort'] == 'mejores') ? 'active' : ''}}">Mejor puntuación</a></li>




        @elseif (Route::currentRouteName() == 'hbo')
            <li><span class="nav-title">Contenido</span></li>
            <li><a href="{{route('hbo', ['type' => 'peliculas'])}}" class="{{($routeInfo['type'] == 'peliculas') ? 'active' : ''}}">Películas de Tv</a></li>
            <li><a href="{{route('hbo', ['type' => 'series'])}}" class="{{($routeInfo['type'] == 'series') ? 'active' : ''}}">Series de Tv</a></li>
            <li><span class="nav-title">Ordenar por</span></li>
            <li><a href="{{route('hbo', ['type' => $type, 'sort' => 'destacadas'])}}" class="{{($routeInfo['sort'] == 'destacadas') ? 'active' : ''}}">Destacadas</a></li>
            <li><a href="{{route('hbo', ['type' => $type, 'sort' => 'populares'])}}" class="{{($routeInfo['sort'] == 'populares') ? 'active' : ''}}">Populares</a></li>
            <li><a href="{{route('hbo', ['type' => $type, 'sort' => 'mejores'])}}" class="{{($routeInfo['sort'] == 'mejores') ? 'active' : ''}}">Mejor puntuación</a></li>




         @endif
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



