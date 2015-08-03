<?php namespace Digbang\Security\Roles;

use Cartalyst\Sentinel\Roles\RoleInterface;
use Doctrine\Common\Collections\Collection;

interface Role extends RoleInterface
{
	/**
	 * @return Collection
	 */
	public function getPermissions();

	/**
	 * Checks if the given role equals itself.
	 *
	 * @param string|Role $role
	 * @return bool
	 */
	public function is($role);
}
