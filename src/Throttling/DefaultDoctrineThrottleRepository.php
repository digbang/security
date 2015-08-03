<?php namespace Digbang\Security\Throttling;

use Digbang\Security\Users\User;

class DefaultDoctrineThrottleRepository extends DoctrineThrottleRepository
{
	/**
	 * {@inheritdoc}
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
	 * {@inheritdoc}
	 */
	protected function createGlobalThrottle()
	{
		return new DefaultGlobalThrottle;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function createIpThrottle($ipAddress)
	{
		return new DefaultIpThrottle($ipAddress);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function createUserThrottle(User $user)
	{
		return new DefaultUserThrottle($user);
	}
}
