<?php namespace Digbang\Security\Throttling;

class DefaultIpThrottle extends DefaultThrottle
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
