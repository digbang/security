<?php namespace Digbang\Security\Persistences;

use Doctrine\Common\Collections\Collection;

trait PersistableTrait
{
	/**
	 * @var Collection
	 */
	protected $persistences;

	/**
	 * @return Collection
	 */
	public function getPersistences()
	{
		return $this->persistences;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getPersistableKey()
	{
		return 'user_id';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPersistableRelationship()
	{
		return 'persistences';
	}

	/**
	 * {@inheritdoc}
	 */
	public function generatePersistenceCode()
	{
		return str_random(32);
	}
}
