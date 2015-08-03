<?php namespace Digbang\Security\Roles;

class DefaultDoctrineRoleRepository extends DoctrineRoleRepository
{
	/**
	 * {@inheritdoc}
	 */
	protected function entityName()
	{
		return DefaultRole::class;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function createRole($name, $slug = null)
	{
		return new DefaultRole($name, $slug);
	}
}
