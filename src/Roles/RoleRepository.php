<?php namespace Digbang\Security\Roles;

use Cartalyst\Sentinel\Roles\RoleRepositoryInterface;
use Doctrine\Common\Collections\Selectable;
use Doctrine\Common\Persistence\ObjectRepository;
use Illuminate\Support\Collection;

interface RoleRepository extends ObjectRepository, RoleRepositoryInterface, Selectable
{
	/**
	 * Creates a role and persists it.
	 *
	 * @param string      $name
	 * @param string|null $slug
	 *
	 * @return Role
	 */
	public function create($name, $slug = null);

	/**
	 * Persist changes to the Role.
	 *
	 * @param Role $role
	 */
	public function save(Role $role);

	/**
	 * Delete the role.
	 *
	 * @param Role $role
	 */
	public function delete(Role $role);

	/**
	 * @return Collection|array
	 */
	public function findAll();
}
