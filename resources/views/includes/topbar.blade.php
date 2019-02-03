<div class="topbar">
    <div class="wrapper">
        <div class="topbar-inner">
            <div class="topbar-main">
                <div class="logo">
                    <span class="icon-tv"></span>
                    <p>lomejordelatv</p>
                </div>
            </div>
            <div class="topbar-nav scroll">
                <ul class="main-nav">
                    <li><a class="television {{(isset($routeInfo) && $routeInfo['channel'] == 'tv') ? 'active' : ''}}" href="{{route('tv', ['type' => 'peliculas', 'channel' => 'tv'])}}">Televisión</a></li>
                    <li><a class="netflix {{(isset($routeInfo) && $routeInfo['channel'] == 'netflix') ? 'active' : ''}}" href="{{route('netflix', ['type' => 'peliculas'])}}">Netflix</a></li>
                    <li><a class="amazon {{(isset($routeInfo) && $routeInfo['channel'] == 'amazon') ? 'active' : ''}}" href="{{route('amazon', ['type' => 'peliculas'])}}">Prime Video</a></li>
                    <li><a class="hbo {{(isset($routeInfo) && $routeInfo['channel'] == 'hbo') ? 'active' : ''}}" href="{{route('hbo', ['type' => 'peliculas'])}}">HBO</a></li>
                </ul>

                <div class="search-box">
                    <form method="GET" action="#" data-url="{{ route('liveSearch') }}" data-path="{{ route('movie', ['slug' => '']) }}">
                        @csrf    
                        <input type="text" class="search-input" name="search" placeholder="Busca por título">
                        <button type="submit" name="search"><span class="icon-search6"></span></button>
                    </form>
                    <div class="search-results"></div>
                </div>

                <div class="scroll-fade"></div>
            </div>
        </div>
    </div>
</div>