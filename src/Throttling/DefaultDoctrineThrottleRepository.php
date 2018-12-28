<?php

namespace Digbang\Security\Throttling;

use Digbang\Security\Users\User;

class DefaultDoctrineThrottleRepository extends DoctrineThrottleRepository
{
    protected const ENTITY_CLASSNAME_GLOBAL = DefaultGlobalThrottle::class;
    protected const ENTITY_CLASSNAME_IP = DefaultIpThrottle::class;
    protected const ENTITY_CLASSNAME_USER = DefaultUserThrottle::class;

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
                return static::ENTITY_CLASSNAME_GLOBAL;
            case 'ip':
                return static::ENTITY_CLASSNAME_IP;
            case 'user':
                return static::ENTITY_CLASSNAME_USER;
        }

        throw new \InvalidArgumentException("Invalid throttle type: [$type]");
    }

    /**
     * {@inheritdoc}
     */
    protected function createGlobalThrottle()
    {
        $entity = static::ENTITY_CLASSNAME_GLOBAL;

        return new $entity();
    }

    /**
     * {@inheritdoc}
     */
    protected function createIpThrottle($ipAddress)
    {
        $entity = static::ENTITY_CLASSNAME_IP;

        return new $entity($ipAddress);
    }

    /**
     * {@inheritdoc}
     */
    protected function createUserThrottle(User $user)
    {
        $entity = static::ENTITY_CLASSNAME_USER;

        return new $entity($user);
    }
}
