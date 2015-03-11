<?php namespace Digbang\Security\Contracts;

use Doctrine\Common\Persistence\ObjectRepository;

interface RepositoryAware
{
	/**
	 * Hack to allow Entities to behave similar to an ActiveRecord pattern.
	 *
	 * @param ObjectRepository $repository
	 * @return void
	 */
	public function setRepository(ObjectRepository $repository);
}
