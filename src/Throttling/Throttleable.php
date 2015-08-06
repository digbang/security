<?php namespace Digbang\Security\Throttling;

use Doctrine\Common\Collections\Collection;

interface Throttleable
{
	/**
	 * @return Collection
	 */
	public function getThrottles();
}
