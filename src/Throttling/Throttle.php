<?php namespace Digbang\Security\Throttling;

use Digbang\Doctrine\TimestampsTrait;
use Digbang\Security\Contracts\Throttle as ThrottleInterface;

abstract class Throttle implements ThrottleInterface
{
	use TimestampsTrait;

	/**
	 * @type int
	 */
	protected $id;

	/**
	 * @return \Carbon\Carbon
	 */
	public function getCreatedAt()
	{
		return $this->createdAt;
	}
}
