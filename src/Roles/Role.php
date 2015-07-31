<?php namespace Digbang\Security\Roles;

use Cartalyst\Sentinel\Roles\RoleInterface;

interface Role extends RoleInterface
{
	public function getPermissions();

	/**
	 * Checks if the given role equals itself.
	 *
	 * @param string|Role $role
	 * @return bool
	 */
	public function is($role);
}
