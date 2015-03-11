<?php namespace Digbang\Security\Contracts;

use Cartalyst\Sentry\Groups\GroupInterface;

interface Group extends GroupInterface
{
	/**
	 * @param string $name
	 * @param array  $permissions
	 *
	 * @return Group
	 */
	public static function create($name, array $permissions = []);

	/**
	 * @param string $name
	 *
	 * @return void
	 */
	public function changeName($name);

	/**
	 * @param array $permissions
	 *
	 * @return void
	 */
	public function setPermissions(array $permissions);
}
