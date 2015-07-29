<?php namespace Digbang\Security\Persistences;

use Cartalyst\Sentinel\Persistences\PersistableInterface;
use Digbang\Security\Contracts\Entities\User;

final class DefaultDoctrinePersistenceRepository extends DoctrinePersistenceRepository
{
	protected function entityName()
	{
		return DefaultPersistence::class;
	}

	/**
	 * @param PersistableInterface $persistable
	 * @param string               $code
	 *
	 * @return DefaultPersistence
	 */
	protected function create(PersistableInterface $persistable, $code)
	{
		if (! $persistable instanceof User)
		{
			throw new \UnexpectedValueException("Default doctrine persistences are only related to the Digbang\\Security\\Contracts\\Entities\\User persistable");
		}

		return new DefaultPersistence($persistable, $code);
	}
}
