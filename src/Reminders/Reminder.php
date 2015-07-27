<?php namespace Digbang\Security\Reminders;

use Carbon\Carbon;
use Digbang\Doctrine\TimestampsTrait;
use Digbang\Security\Users\User;

class Reminder
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
}
