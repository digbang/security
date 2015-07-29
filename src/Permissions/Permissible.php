<?php namespace Digbang\Security\Permissions;

interface Permissible
{
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
}
