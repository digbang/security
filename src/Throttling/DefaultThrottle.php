<?php namespace Digbang\Security\Throttling;

use Digbang\Doctrine\TimestampsTrait;

abstract class DefaultThrottle implements Throttle
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
