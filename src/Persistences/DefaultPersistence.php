<?php namespace Digbang\Security\Persistences;

use Digbang\Doctrine\TimestampsTrait;
use Digbang\Security\Users\User;

class DefaultPersistence implements Persistence
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

	/**
	 * Persistence constructor.
	 *
	 * @param User   $user
	 * @param string $code
	 */
	public function __construct(User $user, $code)
	{
		$this->user = $user;
		$this->code = $code;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getUser()
	{
		return $this->user;
	}
}
