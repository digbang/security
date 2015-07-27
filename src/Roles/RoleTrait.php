<?php namespace Digbang\Security\Roles;

use Digbang\Security\Permissions\PermissionCollection;
use Doctrine\Common\Collections\ArrayCollection;

trait RoleTrait
{
	/**
	 * @type int
	 */
	private $id;

	/**
	 * @type string
	 */
	private $name;

	/**
	 * @type string
	 */
	private $slug;

	/**
	 * @type PermissionCollection
	 */
	private $permissions;

	/**
	 * @type ArrayCollection
	 */
	private $users;

	/**
	 * Returns the group's ID.
	 *
	 * @return mixed
	 */
	public function getRoleId()
	{
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getRoleSlug()
	{
		return $this->slug;
	}

	/**
	 * @return ArrayCollection
	 */
	public function getUsers()
	{
		return $this->users;
	}
}

