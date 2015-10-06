<?php namespace Digbang\Security\Permissions;

interface Permission
{
	/**
	 * @return string
	 */
	public function getName();

	/**
	 * @return bool
	 */
	public function isAllowed();

	/**
	 * Allow this permission.
	 *
	 * @return void
	 */
	public function allow();

	/**
	 * Deny this permission.
	 *
	 * @return void
	 */
	public function deny();

	/**
	 * Compares two permissions and returns TRUE if they are equal.
	 *
	 * @param Permission $permission
	 *
	 * @return boolean
	 */
	public function equals(Permission $permission);
}
