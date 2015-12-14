<?php namespace Digbang\Security\Roles;

use Doctrine\Common\Collections\ArrayCollection;

trait RoleableTrait
{
	/**
	 * @var ArrayCollection|Role[]
	 */
	protected $roles;

	/**
	 * {@inheritdoc}
	 */
	public function getRoles()
	{
		return $this->roles;
	}

	/**
	 * {@inheritdoc}
	 */
	public function inRole($role)
	{
		return $this->roles->exists(function($key, Role $myRole) use ($role){
			return $myRole->is($role);
		});
	}

	/**
	 * {@inheritdoc}
	 */
	public function addRole(Role $role)
	{
		if (! $this->inRole($role))
		{
			$this->roles->add($role);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function removeRole(Role $role)
	{
		$this->roles->removeElement($role);
	}
}
