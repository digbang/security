<?php namespace Digbang\Security\Roles;

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
}
