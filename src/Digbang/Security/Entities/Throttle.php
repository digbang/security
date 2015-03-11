<?php namespace Digbang\Security\Entities;

use Digbang\Security\Contracts\RepositoryAware;
use Digbang\Security\Contracts\User as UserInterface;
use Digbang\Security\Contracts\Throttle as ThrottleInterface;

final class Throttle implements ThrottleInterface, RepositoryAware
{
	use ThrottleTrait;

	/**
	 * @param UserInterface $user
	 * @param string        $ipAddress
	 */
	public function __construct(UserInterface $user, $ipAddress)
	{
		$this->user = $user;
		$this->ipAddress = $ipAddress;
	}

	/**
	 * @param UserInterface $user
	 * @param string        $ipAddress
	 *
	 * @return ThrottleInterface
	 */
	public static function create(UserInterface $user, $ipAddress)
	{
		return new static($user, $ipAddress);
	}
}
