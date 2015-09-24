<?php namespace Digbang\Security\Permissions;

use Cartalyst\Sentinel\Permissions\PermissibleInterface;
use Doctrine\Common\Collections\Collection;

interface Permissible extends PermissibleInterface
{
	/**
	 * Set the Permissions instance factory configured for this context.
	 *
	 * @param \Closure $permissionsFactory
	 * @return void
	 */
	public function setPermissionsFactory(\Closure $permissionsFactory);

	/**
	 * @param string $permission
	 * @return bool
	 */
	public function hasAccess($permission);

	/**
	 * @param string $permission
	 * @return bool
	 */
	public function hasAnyAccess($permission);

	/**
	 * @param string|array $permissions
	 * @param bool         $force
	 */
	public function allow($permissions, $force = false);

	/**
	 * @param string|array $permissions
	 * @param bool         $force
	 */
	public function deny($permissions, $force = false);

	/**
	 * @return Collection
	 */
	public function getPermissions();

	/**
	 * @return void
	 */
	public function clearPermissions();
}
