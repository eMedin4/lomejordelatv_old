@extends('layouts.layout')

@section('content')

	<div class="main-layout">

		<div class="col-1">
			@foreach ($records_4 as $record)
				<article class="record record-3">
					@include('includes.record', [
						'record' => $record, 
						'excerpt' => True, 
						'heading' => 'h3',
						'thumb' => True,
						])
				</article>
			@endforeach
		</div>

		<div class="col-2">
			<article class="record record-1">
				@include('includes.record', [
					'record' => $records_1, 
					'excerpt' => True, 
					'heading' => 'h2',
					'thumb' => True,
					])
			</article>
			<div class="section-2">
				@foreach ($records_3 as $record)
					<article class="record record-2">
						@include('includes.record', [
							'record' => $record, 
							'excerpt' => False, 
							'heading' => 'h4',
							'thumb' => True,
							])
					</article>
				@endforeach
			</div>
			<article class="record record-1">
				@include('includes.record', [
					'record' => $records_2, 
					'excerpt' => True, 
					'heading' => 'h2',
					'thumb' => True,
					])
			</article>
			<div class="section-2">
				@foreach ($records_5 as $record)
					<article class="record record-2">
						@include('includes.record', [
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
			<div class="section-recents">
				@foreach ($recentRecords as $record)
					<article class="recent-record">
						@include('includes.record', [
							'record' => $record, 
							'excerpt' => False, 
							'heading' => 'h5',
							'thumb' => False,
							])
					</article>
				@endforeach
			</div>
		</div>

	</div>

@endsection

@section('scripts')
	
@endsection