<?php namespace Digbang\Security\Entities;

use Digbang\Security\Contracts;

class GroupPermission extends Permission implements Contracts\Permission
{
	use GroupPermissionTrait;

	/**
	 * @param Contracts\Role $group
	 * @param Permission      $permission
	 */
	public function __construct(Contracts\Role $group, $permission)
	{
		parent::__construct($permission);

		$this->group = $group;
	}
}
