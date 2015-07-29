<?php namespace Digbang\Security\Throttling;

interface Throttle
{
	/**
	 * @return \Carbon\Carbon
	 */
	public function getCreatedAt();
}
