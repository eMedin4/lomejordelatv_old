@extends('layouts.layout')

@section('content')

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
			<article class="card card-1">
				@include('includes.card', [
					'record' => $records_1, 
					'excerpt' => True, 
					'heading' => 'h2',
					'thumb' => True,
					])
			</article>
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
			<article class="card card-1">
				@include('includes.card', [
					'record' => $records_2, 
					'excerpt' => True, 
					'heading' => 'h2',
					'thumb' => True,
					])
			</article>
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
			<div class="section-title">
				<span class="icon-tv"></span> 
				<p>Ahora en TV</p>
			</div>
		</div>

	</div>

@endsection

@section('scripts')
	
@endsection