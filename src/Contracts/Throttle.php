<?php namespace Digbang\Security\Contracts;

interface Throttle
{
	/**
	 * @return \Carbon\Carbon
	 */
	public function getCreatedAt();
}
