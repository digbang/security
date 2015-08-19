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
		return [];
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
}
