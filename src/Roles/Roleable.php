<?php namespace Digbang\Security\Roles;

use Cartalyst\Sentinel\Roles\RoleableInterface;

interface Roleable extends RoleableInterface
{
	/**
	 * Add a role to the entity.
	 *
	 * @param Role $role
	 * @return void
	 */
	public function addRole(Role $role);

	/**
	 * Remove a role from the entity.
	 *
	 * @param Role $role
	 * @return void
	 */
	public function removeRole(Role $role);
}
