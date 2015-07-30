<?php namespace Digbang\Security\Persistences;

use Digbang\Security\Users\User;

final class DefaultDoctrinePersistenceRepository extends DoctrinePersistenceRepository
{
	/**
	 * {@inheritdoc}
	 */
	protected function entityName()
	{
		return DefaultPersistence::class;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function create(User $user, $code)
	{
		return new DefaultPersistence($user, $code);
	}
}
