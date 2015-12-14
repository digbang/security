<?php namespace Digbang\Security\Persistences;

use Digbang\Security\Support\TimestampsTrait;
use Digbang\Security\Users\User;

class DefaultPersistence implements Persistence
{
	use TimestampsTrait;

	/**
	 * @var int
	 */
	private $id;

	/**
	 * @var User
	 */
	private $user;

	/**
	 * @var string
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

	/**
	 * @return string
	 */
	public function getCode()
	{
		return $this->code;
	}

	/**
	 * @return \Carbon\Carbon
	 */
	public function getCreatedAt()
	{
		return $this->createdAt;
	}

	/**
	 * @return \Carbon\Carbon
	 */
	public function getUpdatedAt()
	{
		return $this->updatedAt;
	}
}
