<?php

namespace Digbang\Security\Users;

class DefaultDoctrineUserRepository extends DoctrineUserRepository
{
    protected const ENTITY_CLASSNAME = DefaultUser::class;

    /**
     * Get the User class name.
     *
     * @return string
     */
    protected function entityName()
    {
        return static::ENTITY_CLASSNAME;
    }

    /**
     * Create a new user based on the given credentials.
     *
     * @param array $credentials
     *
     * @return User
     */
    protected function createUser(array $credentials)
    {
        $entity = static::ENTITY_CLASSNAME;

        if (count(array_only($credentials, ['email', 'password', 'username'])) < 3)
        {
            throw new \InvalidArgumentException("Missing arguments.");
        }

        /** @var User $user */
        $user = new $entity(
            $credentials['email'],
            $credentials['password'],
            $credentials['username']
        );

        $rest = array_except($credentials, ['email', 'username', 'password']);
        if (! empty($rest))
        {
            $user->update($rest);
        }

        return $user;
    }
}
