<?php namespace Digbang\Security\Throttling;

use Doctrine\Common\Collections\ArrayCollection;

trait ThrottleableTrait
{
	/**
	 * @var ArrayCollection
	 */
	protected $throttles;

	/**
	 * @return ArrayCollection
	 */
	public function getThrottles()
	{
		return $this->throttles;
	}
}
