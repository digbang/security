<?php namespace Digbang\Security\Reminders;

use Carbon\Carbon;
use Digbang\Doctrine\TimestampsTrait;
use Digbang\Security\Users\User;

class DefaultReminder implements Reminder
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
	 * DefaultReminder constructor.
	 * @param User $user
	 */
	public function __construct(User $user)
	{
		$this->user = $user;
		$this->code = str_random(32);
	}

	public function complete()
	{
		$this->completed = true;
		$this->completedAt = Carbon::now();
	}
}
