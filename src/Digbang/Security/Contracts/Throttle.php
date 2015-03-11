<?php namespace Digbang\Security\Contracts;

interface Throttle
{
	/**
	 * @param User   $user
	 * @param string $ipAddress
	 *
	 * @return Throttle
	 */
	public static function create(User $user, $ipAddress);
}
