<?php namespace Digbang\Security\Users;

use Cartalyst\Sentinel\Persistences\PersistableInterface;
use Cartalyst\Sentinel\Users\UserInterface;

interface User extends UserInterface, PersistableInterface
{
	/**
	 * @param array $credentials
	 * @return void
	 */
	public function update(array $credentials);

	/**
	 * @param string $password
	 * @return bool
	 */
	public function checkPassword($password);

	/**
	 * @return void
	 */
	public function recordLogin();
}