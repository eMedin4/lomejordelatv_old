@extends('layouts.layout')

@section('content')



	<div class="main-layout">

		<div class="col-1">
			@foreach ($records['records_4'] as $record)
				<article class="card card-3">
					@include('includes.card', [
						'record' => $record[0],
						'recordsCollection' => $record,
						'excerpt' => 'excerpt200', 
						'heading' => 'h3',
						'thumb' => 'sml',
						])
				</article>
			@endforeach
		</div>

		<div class="col-2">
			@if ($records['records_1'])
				<article class="card card-1">
					@include('includes.card', [
						'record' => $records['records_1'][0], 
						'recordsCollection' => $records['records_1'],
						'excerpt' => 'excerpt400', 
						'heading' => 'h2',
						'thumb' => 'lrg',
						])
				</article>
			@endif
			<div class="card-2-wrap">
				@foreach ($records['records_3'] as $record)
					<article class="card card-2">
						@include('includes.card', [
							'record' => $record[0],
							'recordsCollection' => $record,
							'excerpt' => False, 
							'heading' => 'h4',
							'thumb' => 'sml',
							])
					</article>
				@endforeach
			</div>
			@if ($records['records_2'])
				<article class="card card-1">
					@include('includes.card', [
						'record' => $records['records_2'][0], 
						'recordsCollection' => $records['records_2'],
						'excerpt' => 'excerpt400', 
						'heading' => 'h2',
						'thumb' => 'lrg',
						])
				</article>
			@endif
			<div class="card-2-wrap">
				@foreach ($records['records_5'] as $record)
					<article class="card card-2">
						@include('includes.card', [
							'record' => $record[0],
							'recordsCollection' => $record,
							'excerpt' => False, 
							'heading' => 'h4',
							'thumb' => 'sml',
							])
					</article>
				@endforeach
			</div>

		</div>

		<div class="col-3">
			@include('includes.sidebar', $parameters)
		</div>

	</div>

@endsection

@section('scripts')
	
@endsection