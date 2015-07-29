<?php namespace Digbang\Security\Permissions;

use Cartalyst\Sentinel\Permissions\PermissionsInterface;
use Cartalyst\Sentinel\Permissions\StandardPermissions;

class DoctrineStandardPermissions implements PermissionsInterface
{
	/**
	 * @type StandardPermissions
	 */
	private $standardPermissions;

	/**
	 * DoctrineStandardPermissions constructor.
	 *
	 * @param StandardPermissions $standardPermissions
	 */
	public function __construct(StandardPermissions $standardPermissions)
	{
		$this->standardPermissions = $standardPermissions;
	}

	/**
	 * Returns if access is available for all given permissions.
	 *
	 * @param  array|string $permissions
	 *
	 * @return bool
	 */
	public function hasAccess($permissions)
	{
		return $this->standardPermissions->hasAccess($permissions);
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
		return $this->standardPermissions->hasAnyAccess($permissions);
	}
}
