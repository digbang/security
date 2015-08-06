<?php namespace Digbang\Security\Persistences;

use Cartalyst\Sentinel\Persistences\PersistableInterface;
use Doctrine\Common\Collections\Collection;

interface Persistable extends PersistableInterface
{
	/**
	 * @return Collection
	 */
	public function getPersistences();
}
