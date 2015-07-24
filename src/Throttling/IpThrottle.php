<?php namespace Digbang\Security\Throttling;

class IpThrottle extends Throttle
{
	/**
	 * @type string
	 */
	private $ip;

	/**
	 * IpThrottle constructor.
	 *
	 * @param string $ip
	 */
	public function __construct($ip)
	{
		$this->ip = $ip;
	}
}
