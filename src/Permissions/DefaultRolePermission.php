<?php namespace Digbang\Security\Permissions;

use Digbang\Security\Roles\Role;

class DefaultRolePermission extends DefaultPermission
{
	/**
	 * @var Role
	 */
	private $role;

	/**
	 * @param Role   $role
	 * @param string $name
	 * @param bool   $allowed
	 */
	public function __construct(Role $role, $name, $allowed = true)
	{
		parent::__construct($name, $allowed);

		$this->role = $role;
	}

	/**
	 * @return Role
	 */
	public function getRole()
	{
		return $this->role;
	}
}
