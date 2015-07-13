<?php namespace Digbang\Security\Entities;

use Digbang\Security\Contracts\User as UserInterface;
use Digbang\Security\Contracts\RepositoryAware;
use Doctrine\Common\Collections\ArrayCollection;

class User implements UserInterface, RepositoryAware
{
	use UserTrait;

	/**
	 * @param string $email
	 * @param string $password
	 */
	public function __construct($email, $password)
	{
		$this->email    = $email;
		$this->password = $password;

		$this->groups      = new ArrayCollection;
		$this->permissions = new ArrayCollection;
	}

	/**
	 * @param string $email
	 * @param string $password
	 *
	 * @return User
	 */
	public static function createFromCredentials($email, $password)
	{
		return new static($email, $password);
	}
}
