<?php namespace Digbang\Security\Users;

final class DefaultDoctrineUserRepository extends DoctrineUserRepository
{
	/**
	 * Get the User class name.
	 *
	 * @return string
	 */
	protected function entityName()
	{
		return DefaultUser::class;
	}

	/**
	 * Create a new user based on the given credentials.
	 *
	 * @param array $credentials
	 *
	 * @return DefaultUser
	 */
	protected function createUser(array $credentials)
	{
		if (count(array_only($credentials, ['email', 'password', 'username'])) < 3)
		{
			throw new \InvalidArgumentException("Missing arguments.");
		}

		$user = new DefaultUser(
			new ValueObjects\Email($credentials['email']),
			new ValueObjects\Password($credentials['password']),
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
