<?php namespace Digbang\Security\Throttling;

class DefaultIpThrottle extends DefaultThrottle
{
	/**
	 * @var string
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

	/**
	 * @return string
	 */
	public function getIp()
	{
		return $this->ip;
	}
}
