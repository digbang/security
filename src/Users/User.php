<?php namespace Digbang\Security\Users;

use Digbang\Doctrine\TimestampsTrait;
use Digbang\Security\Contracts\User as UserInterface;
use Digbang\Security\Contracts\RepositoryAware;

class User implements UserInterface, RepositoryAware
{
	use TimestampsTrait;
	use UserTrait;

	/**
	 * @param string $email
	 * @param string $password
	 */
	public function __construct($email, $password)
	{
		$this->email    = $email;
		$this->password = $password;
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
