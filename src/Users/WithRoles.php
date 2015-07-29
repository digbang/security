<?php namespace Digbang\Security\Users;

use Digbang\Security\Roles\DefaultRole;
use Illuminate\Support\Collection;

interface WithRoles
{
	/**
	 * @return Collection
	 */
	public function getRoles();

	/**
	 * @param DefaultRole|string $role
	 *
*@return bool
	 */
	public function inRole($role);
}
