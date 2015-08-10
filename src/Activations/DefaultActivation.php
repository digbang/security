<?php namespace Digbang\Security\Activations;

use Carbon\Carbon;
use Digbang\Doctrine\TimestampsTrait;
use Digbang\Security\Users\User;

class DefaultActivation implements Activation
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
	 * @type bool
	 */
	private $completed = false;

	/**
	 * @type Carbon
	 */
	private $completedAt;

	/**
	 * Activation constructor.
	 *
	 * @param User $user
	 */
	public function __construct(User $user)
	{
		$this->user = $user;
		$this->code = str_random(32);
	}

	/**
	 * @return void
	 */
	public function complete()
	{
		$this->completed = true;
		$this->completedAt = Carbon::now();
	}

	/**
	 * {@inheritdoc}
	 */
	public function __get($name)
	{
		if ($name == 'code')
		{
			return $this->code;
		}

		throw new \BadMethodCallException("Property '$name' does not exist or is inaccessible.");
	}

	/**
	 * @return Carbon
	 */
	public function getCompletedAt()
	{
		return $this->completedAt;
	}

	/**
	 * @return User
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * @return bool
	 */
	public function isCompleted()
	{
		return $this->completed;
	}

	/**
	 * @return \Carbon\Carbon
	 */
	public function getUpdatedAt()
	{
		return $this->updatedAt;
	}

	/**
	 * @return \Carbon\Carbon
	 */
	public function getCreatedAt()
	{
		return $this->createdAt;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCode()
	{
		return $this->code;
	}
}
