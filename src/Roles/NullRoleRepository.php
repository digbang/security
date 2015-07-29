<?php namespace Digbang\Security\Roles;

use Cartalyst\Sentinel\Roles\RoleRepositoryInterface;

class NullRoleRepository implements RoleRepositoryInterface
{
	/**
	 * Finds a role by the given primary key.
	 *
	 * @param  int $id
	 * @return \Cartalyst\Sentinel\Roles\RoleInterface
	 */
	public function findById($id)
	{
		return null;
	}

	/**
	 * Finds a role by the given slug.
	 *
	 * @param  string $slug
	 * @return \Cartalyst\Sentinel\Roles\RoleInterface
	 */
	public function findBySlug($slug)
	{
		return null;
	}

	/**
	 * Finds a role by the given name.
	 *
	 * @param  string $name
	 * @return \Cartalyst\Sentinel\Roles\RoleInterface
	 */
	public function findByName($name)
	{
		return null;
	}
}
