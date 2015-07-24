<?php namespace Digbang\Security\Throttling;

use Cartalyst\Sentinel\Users\UserInterface;
use Digbang\Security\Contracts\Factories\ThrottleFactory;

final class DefaultThrottleFactory implements ThrottleFactory
{
	/**
     * @return GlobalThrottle
     */
    public function createGlobalThrottle()
    {
        return new GlobalThrottle;
    }

    /**
     * @param $ipAddress
     *
     * @return IpThrottle
     */
    public function createIpThrottle($ipAddress)
    {
        return new IpThrottle($ipAddress);
    }

    /**
     * @param UserInterface $user
     *
     * @return UserThrottle
     */
    public function createUserThrottle(UserInterface $user)
    {
        return new UserThrottle($user);
    }
}
