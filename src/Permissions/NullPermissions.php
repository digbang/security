<?php namespace Digbang\Security\Permissions;

use Cartalyst\Sentinel\Permissions\PermissionsInterface;

final class NullPermissions implements PermissionsInterface
{
	/**
	 * Returns if access is available for all given permissions.
	 *
	 * @param  array|string $permissions
	 *
	 * @return bool
	 */
	public function hasAccess($permissions)
	{
		return false;
	}

	/**
	 * Returns if access is available for any given permissions.
	 *
	 * @param  array|string $permissions
	 *
	 * @return bool
	 */
	public function hasAnyAccess($permissions)
	{
		return false;
	}
}
