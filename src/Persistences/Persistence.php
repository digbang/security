<?php namespace Digbang\Security\Persistences;

use Digbang\Doctrine\TimestampsTrait;
use Digbang\Security\Contracts\User;

class Persistence
{
	use TimestampsTrait;

	/**
	 * @type int
	 */
	private $id;

	/**
	 * @type User
	 */
	private $user;

	/**
	 * @type string
	 */
	private $code;
}
