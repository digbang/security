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
}
