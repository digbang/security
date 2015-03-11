<?php namespace Digbang\Security\Entities;

use Carbon\Carbon;
use Cartalyst\Sentry\Throttling\UserBannedException;
use Cartalyst\Sentry\Throttling\UserSuspendedException;

trait ThrottleTrait
{
	/**
	 * Attempt limit.
	 *
	 * @var int
	 */
	protected static $attemptLimit = 5;

	/**
	 * Suspensions time in minutes.
	 *
	 * @var int
	 */
	protected static $suspensionTime = 15;

	/**
	 * @type int
	 */
	private $id;

	/**
	 * @type User
	 */
	private $user;

	/**
	 * @type int
	 */
	private $attempts = 0;

	/**
	 * @type string
	 */
	private $ipAddress;

	/**
	 * @type bool
	 */
	private $suspended = false;

	/**
	 * @type boolean
	 */
	private $banned = false;

	/**
	 * @type \Carbon\Carbon
	 */
	private $lastAttemptAt;

	/**
	 * @type \Carbon\Carbon
	 */
	private $suspendedAt;

	/**
	 * @type \Carbon\Carbon
	 */
	private $bannedAt;

	/**
	 * This is needed to emulate an AR behavior.
	 * Sentry uses AR to save/delete entities...
	 *
	 * @type \Digbang\Security\Repositories\DoctrineThrottleRepository
	 */
	private $throttleRepository;

	/**
	 * @param \Digbang\Security\Repositories\DoctrineThrottleRepository $throttleRepository
	 */
	public function setRepository(\Doctrine\Common\Persistence\ObjectRepository $throttleRepository)
	{
		$this->throttleRepository = $throttleRepository;
	}

	/**
	 * Returns the associated user with the throttler.
	 *
	 * @return \Digbang\Security\Contracts\User
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * Get the current amount of attempts.
	 *
	 * @return int
	 */
	public function getLoginAttempts()
	{
		if ($this->attempts > 0 and $this->lastAttemptAt)
		{
			$this->clearLoginAttemptsIfAllowed();
		}

		return $this->attempts;
	}

	/**
	 * Add a new login attempt.
	 *
	 * @return void
	 */
	public function addLoginAttempt()
	{
		$this->attempts++;
		$this->lastAttemptAt = new Carbon;

		if ($this->getLoginAttempts() >= static::$attemptLimit)
		{
			$this->suspend();
		}
		else
		{
			$this->save();
		}
	}

	/**
	 * Clear all login attempts
	 *
	 * @return void
	 */
	public function clearLoginAttempts()
	{
		if ($this->getLoginAttempts() == 0 or $this->suspended)
		{
			return;
		}

		$this->attempts      = 0;
		$this->lastAttemptAt = null;
		$this->suspended     = false;
		$this->suspendedAt   = null;
		$this->save();
	}

	/**
	 * Suspend the user associated with the throttle
	 *
	 * @return void
	 */
	public function suspend()
	{
		if (! $this->suspended)
		{
			$this->suspended   = true;
			$this->suspendedAt = new Carbon;
			$this->save();
		}
	}

	/**
	 * Unsuspend the user.
	 *
	 * @return void
	 */
	public function unsuspend()
	{
		if ($this->suspended)
		{
			$this->attempts      = 0;
			$this->lastAttemptAt = null;
			$this->suspended     = false;
			$this->suspendedAt   = null;
			$this->save();
		}
	}

	/**
	 * Check if the user is suspended.
	 *
	 * @return bool
	 */
	public function isSuspended()
	{
		if ($this->suspended and $this->suspendedAt)
		{
			$this->removeSuspensionIfAllowed();

			return (bool) $this->suspended;
		}

		return false;
	}

	/**
	 * Ban the user.
	 *
	 * @return bool
	 */
	public function ban()
	{
		if (! $this->banned)
		{
			$this->banned   = true;
			$this->bannedAt = new Carbon;
			$this->save();
		}
	}

	/**
	 * Unban the user.
	 *
	 * @return void
	 */
	public function unban()
	{
		if ($this->banned)
		{
			$this->banned   = false;
			$this->bannedAt = null;
			$this->save();
		}
	}

	/**
	 * Check if user is banned
	 *
	 * @return bool
	 */
	public function isBanned()
	{
		return (boolean) $this->banned;
	}

	/**
	 * Check user throttle status.
	 *
	 * @return bool
	 * @throws UserBannedException
	 * @throws UserSuspendedException
	 */
	public function check()
	{
		if ($this->isBanned())
		{
			throw new UserBannedException(
				sprintf(
					'User [%s] has been banned.',
					$this->getUser()->getLogin()
				)
			);
		}

		if ($this->isSuspended())
		{
			throw new UserSuspendedException(
				sprintf(
					'User [%s] has been suspended.',
					$this->getUser()->getLogin()
				)
			);
		}

		return true;
	}

	/**
	 * Saves the throttle.
	 *
	 * @return bool
	 */
	public function save()
	{
		$this->throttleRepository->save($this);

		return true;
	}

	/**
	 * Inspects the last attempt vs the suspension time
	 * (the time in which attempts must space before the
	 * account is suspended). If we can clear our attempts
	 * now, we'll do so and save.
	 *
	 * @return void
	 */
	public function clearLoginAttemptsIfAllowed()
	{
		$lastAttempt = clone $this->lastAttemptAt;

		$suspensionTime  = static::$suspensionTime;
		$clearAttemptsAt = $lastAttempt->modify("+{$suspensionTime} minutes");
		$now             = new Carbon;

		if ($clearAttemptsAt <= $now)
		{
			$this->attempts = 0;
			$this->save();
		}

		unset($lastAttempt);
		unset($clearAttemptsAt);
		unset($now);
	}

	/**
	 * Inspects to see if the user can become unsuspended
	 * or not, based on the suspension time provided. If so,
	 * unsuspends.
	 *
	 * @return void
	 */
	public function removeSuspensionIfAllowed()
	{
		$suspended = clone $this->suspendedAt;

		$suspensionTime = static::$suspensionTime;
		$unsuspendedAt  = $suspended->modify("+{$suspensionTime} minutes");
		$now            = new Carbon;

		if ($unsuspendedAt <= $now)
		{
			$this->unsuspend();
		}

		unset($suspended);
		unset($unsuspendedAt);
		unset($now);
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return int
	 */
	public function getAttempts()
	{
		return $this->attempts;
	}

	/**
	 * @return string
	 */
	public function getIpAddress()
	{
		return $this->ipAddress;
	}

	/**
	 * @return Carbon
	 */
	public function getLastAttemptAt()
	{
		return $this->lastAttemptAt;
	}

	/**
	 * @return Carbon
	 */
	public function getSuspendedAt()
	{
		return $this->suspendedAt;
	}

	/**
	 * @return Carbon
	 */
	public function getBannedAt()
	{
		return $this->bannedAt;
	}

	/**
	 * @return \Digbang\Security\Repositories\DoctrineThrottleRepository
	 */
	public function getThrottleRepository()
	{
		return $this->throttleRepository;
	}
}
