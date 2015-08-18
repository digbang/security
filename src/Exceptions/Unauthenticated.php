<?php namespace Digbang\Security\Exceptions;

use Digbang\Security\Contracts\SecurityApi;

class Unauthenticated extends SecurityException
{
	public static function guest(SecurityApi $security)
	{
		$e = new static("User is not logged in.");

		$e->setSecurity($security);

		return $e;
	}
}
