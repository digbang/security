<?php namespace Digbang\Security\Persistences;

use Cartalyst\Sentinel\Persistences\PersistenceInterface;

interface Persistence extends PersistenceInterface
{
	/**
	 * @return \Digbang\Security\Users\User
	 */
	public function getUser();
}
