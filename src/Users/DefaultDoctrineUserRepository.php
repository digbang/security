<?php

namespace Digbang\Security\Users;

use Illuminate\Support\Arr;

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

        if (count(Arr::only($credentials, ['email', 'password', 'username'])) < 3) {
            throw new \InvalidArgumentException('Missing arguments.');
        }

        /** @var User $user */
        $user = new $entity(
            $credentials['email'],
            $credentials['password'],
            $credentials['username']
        );

        $rest = Arr::only($credentials, ['email', 'username', 'password']);
        if (! empty($rest)) {
            $user->update($rest);
        }

        return $user;
    }
}
