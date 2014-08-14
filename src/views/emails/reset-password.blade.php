@extends("l4-backoffice::emails.layout")

@section('title')
	{{ Lang::get('l4-backoffice::emails.forgot-password.title', ['name' => $name]) }}
@stop

@section('body')
	<p>
		{{ Lang::get('l4-backoffice::emails.forgot-password.text') }}:
		<a href="{{ $link }}">{{ $link }}</a>
	</p>
@stop