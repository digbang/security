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
*@return DefaultUser
	 */
	protected function createUser(array $credentials)
	{
		$user = new DefaultUser($credentials['email'], $credentials['username'], $credentials['password']);

		$rest = array_except($credentials, ['email', 'username', 'password']);
		if (! empty($rest))
		{
			$user->update($rest);
		}

		return $user;
	}

}
