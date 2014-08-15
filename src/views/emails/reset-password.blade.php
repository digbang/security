@extends("security::emails.layout")

@section('title')
	{{ Lang::get('security::emails.reset-password.title', ['name' => $name]) }}
@stop

@section('body')
	<p>
		{{ Lang::get('security::emails.reset-password.text') }}:
		<a href="{{ $link }}">{{ $link }}</a>
	</p>
@stop