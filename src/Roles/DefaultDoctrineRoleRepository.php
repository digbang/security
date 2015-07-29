<?php namespace Digbang\Security\Roles;

class DefaultDoctrineRoleRepository extends DoctrineRoleRepository
{
	/**
	 * Get the entity name for this repository.
	 * This entity MUST implement \Digbang\Security\Contracts\Entities\Role
	 *
	 * @return string
	 */
	protected function entityName()
	{
		return DefaultRole::class;
	}
}
