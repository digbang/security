@extends("digbang.security.emails.layout")

@section('title')
	{{ trans('digbang.security.emails.reset-password.title', ['name' => $user->getFirstName() ?: $user->getUserLogin()]) }}
@stop

@section('body')
	<p>
		{{ trans('digbang.security.emails.reset-password.text') }}:
		<a href="{{ $link }}">{{ $link }}</a>
	</p>
@stop