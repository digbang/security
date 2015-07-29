<?php namespace Digbang\Security\Throttling;

use Digbang\Security\Users\User;

class DefaultDoctrineThrottleRepository extends DoctrineThrottleRepository
{

	/**
	 * Get the FQCN of each Throttle type:
	 *   - null: Base throttle type (eg: Digbang\Security\Throttling\DefaultThrottle)
	 *   - 'global': Global throttle type (eg: Digbang\Security\Throttling\DefaultGlobalThrottle)
	 *   - 'ip': Ip throttle type (eg: Digbang\Security\Throttling\DefaultIpThrottle)
	 *   - 'user': User throttle type (eg: Digbang\Security\Throttling\DefaultUserThrottle)
	 *
	 * @param string|null $type
	 *
	 * @return string
	 */
	protected function entityName($type = null)
	{
		if (!$type)
		{
			return DefaultThrottle::class;
		}

		switch ($type)
		{
			case 'global':
				return DefaultGlobalThrottle::class;
			case 'ip':
				return DefaultIpThrottle::class;
			case 'user':
				return DefaultUserThrottle::class;
		}

		throw new \InvalidArgumentException("Invalid throttle type: [$type]");
	}

	/**
	 * Create a GlobalThrottle object
	 *
	 * @return Throttle
	 */
	protected function createGlobalThrottle()
	{
		return new DefaultGlobalThrottle;
	}

	/**
	 * Create an IpThrottle object
	 *
	 * @param string $ipAddress
	 *
	 * @return Throttle
	 */
	protected function createIpThrottle($ipAddress)
	{
		return new DefaultIpThrottle($ipAddress);
	}

	/**
	 * Create a UserThrottle object
	 *
	 * @param User $user
	 *
	 * @return Throttle
	 */
	protected function createUserThrottle(User $user)
	{
		return new DefaultUserThrottle($user);
	}
}
