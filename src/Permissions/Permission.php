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
}
