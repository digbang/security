<?php namespace Digbang\Security\Users;

use Digbang\Doctrine\TimestampsTrait;
use Digbang\Security\Contracts\Entities\Role;
use Digbang\Security\Permissions\Permissible;
use Illuminate\Support\Collection;

class DefaultUser implements User, Permissible, WithRoles
{
	use TimestampsTrait;
	use UserTrait;

	/**
	 * @param string $email
	 * @param string $username
	 * @param string $password
	 */
	public function __construct($email, $username, $password)
	{
		$this->email    = $email;
		$this->username = $username;
		$this->password = $password;
	}

	/**
	 * @param string $permission
	 *
	 * @return bool
	 */
	public function hasAccess($permission)
	{
		// TODO: Implement hasAccess() method.
	}

	/**
	 * @param string $permission
	 *
	 * @return bool
	 */
	public function hasAnyAccess($permission)
	{
		// TODO: Implement hasAnyAccess() method.
	}

	/**
	 * @return Collection
	 */
	public function getRoles()
	{
		// TODO: Implement getRoles() method.
	}

	/**
	 * @param Role|string $role
	 *
	 * @return bool
	 */
	public function inRole($role)
	{
		// TODO: Implement inRole() method.
	}

	/**
	 * @param array $credentials
	 *
	 * @return void
	 */
	public function update(array $credentials)
	{
		// TODO: Implement update() method.
	}

	/**
	 * Returns the user primary key.
	 *
	 * @return int
	 */
	public function getUserId()
	{
		// TODO: Implement getUserId() method.
	}

	/**
	 * Returns the user login.
	 *
	 * @return string
	 */
	public function getUserLogin()
	{
		// TODO: Implement getUserLogin() method.
	}

	/**
	 * Returns the user login attribute name.
	 *
	 * @return string
	 */
	public function getUserLoginName()
	{
		// TODO: Implement getUserLoginName() method.
	}

	/**
	 * Returns the user password.
	 *
	 * @return string
	 */
	public function getUserPassword()
	{
		// TODO: Implement getUserPassword() method.
	}

	/**
	 * @param string $email
	 * @param string $password
	 *
	 * @return User
	 */
	public static function createFromCredentials($email, $password)
	{
		// TODO: Implement createFromCredentials() method.
	}
}
