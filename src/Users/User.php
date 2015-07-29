<?php namespace Digbang\Security\Users;

use Cartalyst\Sentinel\Users\UserInterface;

interface User extends UserInterface
{
	/**
	 * @param string $email
	 * @param string $password
	 *
	 * @return User
	 */
	public static function createFromCredentials($email, $password);

	/**
	 * @param array $credentials
	 * @return void
	 */
	public function update(array $credentials);
}
