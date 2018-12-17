@extends('layouts.layout')

@section('content')

    <div class="headline">
        <div class="main-wrap headline-wrap">
            <div class="headline-content">
                <h1>Peliculas TV</h1>
                <p>Todas las películas que emiten en televisión, ordenadas y clasificadas según su puntuación en IMDB y Filmaffinity.</p>
            </div>
            <!-- imagen --><div class="imagen"></div>
        </div>
    </div>

	<div class="main-layout">

		<div class="col-1">
			@foreach ($records_4 as $record)
				<article class="card card-3">
					@include('includes.card', [
						'record' => $record, 
						'excerpt' => True, 
						'heading' => 'h3',
						'thumb' => True,
						])
				</article>
			@endforeach
		</div>

		<div class="col-2">
			@if ($records_1)
				<article class="card card-1">
					@include('includes.card', [
						'record' => $records_1, 
						'excerpt' => True, 
						'heading' => 'h2',
						'thumb' => True,
						])
				</article>
			@endif
			<div class="card-2-wrap">
				@foreach ($records_3 as $record)
					<article class="card card-2">
						@include('includes.card', [
							'record' => $record, 
							'excerpt' => False, 
							'heading' => 'h4',
							'thumb' => True,
							])
					</article>
				@endforeach
			</div>
			@if ($records_2)
				<article class="card card-1">
					@include('includes.card', [
						'record' => $records_2, 
						'excerpt' => True, 
						'heading' => 'h2',
						'thumb' => True,
						])
				</article>
			@endif
			<div class="card-2-wrap">
				@foreach ($records_5 as $record)
					<article class="card card-2">
						@include('includes.card', [
							'record' => $record, 
							'excerpt' => False, 
							'heading' => 'h4',
							'thumb' => True,
							])
					</article>
				@endforeach
			</div>

		</div>

		<div class="col-3">
			@include('includes.sidebar')
		</div>

	</div>

@endsection

@section('scripts')
	
@endsection