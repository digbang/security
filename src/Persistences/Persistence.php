<?php namespace Digbang\Security\Persistences;

use Cartalyst\Sentinel\Persistences\PersistenceInterface;
use Digbang\Security\Users\User;

interface Persistence extends PersistenceInterface
{
	/**
	 * @return User
	 */
	public function getUser();
}
