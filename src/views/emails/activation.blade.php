@extends("security::emails.layout")

@section('title')
	{{ Lang::get('security::emails.activation.title', ['name' => $name]) }}
@stop

@section('body')
	<p>
		{{ Lang::get('security::emails.activation.text') }}:
		<a href="{{ $link }}">{{ $link }}</a>
	</p>
@stop