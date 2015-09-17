<?php namespace Digbang\Security\Roles;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;

class NullRoleRepository implements RoleRepository
{
	/**
	 * {@inheritdoc}
	 */
	public function findById($id)
	{
		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function findBySlug($slug)
	{
		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function findByName($name)
	{
		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function find($id)
	{
		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function findAll()
	{
		return new \Illuminate\Support\Collection;
	}

	/**
	 * {@inheritdoc}
	 */
	public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
	{
		return [];
	}

	/**
	 * {@inheritdoc}
	 */
	public function findOneBy(array $criteria)
	{
		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getClassName()
	{
		return Role::class;
	}

	/**
	 * Selects all elements from a selectable that match the expression and
	 * returns a new collection containing these elements.
	 *
	 * @param Criteria $criteria
	 *
	 * @return Collection
	 */
	public function matching(Criteria $criteria)
	{
		return [];
	}

	/**
	 * Creates a role and persists it.
	 *
	 * @param string      $name
	 * @param string|null $slug
	 *
	 * @return Role
	 */
	public function create($name, $slug = null)
	{
		throw new \BadMethodCallException(
			"Cannot create role [$name], Roles are disabled. " .
			"Enable Roles through the configuration and try again."
		);
	}

	/**
	 * Persist changes to the Role.
	 *
	 * @param Role $role
	 */
	public function save(Role $role)
	{
		throw new \BadMethodCallException(
			"Cannot save role, Roles are disabled. " .
			"Enable Roles through the configuration and try again."
		);
	}

	/**
	 * Delete the role.
	 *
	 * @param Role $role
	 */
	public function delete(Role $role)
	{
		throw new \BadMethodCallException(
			"Cannot delete role, Roles are disabled. " .
			"Enable Roles through the configuration and try again."
		);
	}
}
