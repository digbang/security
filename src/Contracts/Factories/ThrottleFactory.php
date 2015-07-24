<?php namespace Digbang\Security\Contracts\Factories;

use Cartalyst\Sentinel\Users\UserInterface;
use Digbang\Security\Contracts\Throttle;

interface ThrottleFactory
{
	/**
	 * @return Throttle
	 */
	public function createGlobalThrottle();

	/**
	 * @param $ipAddress
	 * @return Throttle
	 */
	public function createIpThrottle($ipAddress);

	/**
	 * @param UserInterface $user
	 * @return Throttle
	 */
	public function createUserThrottle(UserInterface $user);
}
