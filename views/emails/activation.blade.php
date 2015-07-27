@extends("digbang.security.emails.layout")

@section('title')
	{{ Lang::get('digbang.security.emails.activation.title', ['name' => $user->getFirstName() ?: $user->getUserLogin()]) }}
@stop

@section('body')
	<p>
		{{ Lang::get('digbang.security.emails.activation.text') }}:
		<a href="{{ $link }}">{{ $link }}</a>
	</p>
@stop
