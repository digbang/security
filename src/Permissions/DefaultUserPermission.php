<?php namespace Digbang\Security\Permissions;

use Digbang\Security\Users\User;

class DefaultUserPermission extends DefaultPermission
{
	/**
	 * @var User
	 */
	private $user;

	/**
	 * @param User   $user
	 * @param string $name
	 * @param bool   $allowed
	 */
	public function __construct(User $user, $name, $allowed = true)
	{
		parent::__construct($name, $allowed);

		$this->user = $user;
	}

	/**
	 * @return User
	 */
	public function getUser()
	{
		return $this->user;
	}
}
