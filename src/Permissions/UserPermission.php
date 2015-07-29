<?php namespace Digbang\Security\Permissions;

class UserPermission
{
	use UserPermissionTrait;

	/**
	 * @param Contracts\User $user
	 * @param string         $permission
	 * @param bool           $allowed
	 */
	public function __construct(Contracts\User $user, $permission, $allowed = true)
	{
		parent::__construct($permission);

		$this->user = $user;
		$this->allowed = $allowed;
	}
}
