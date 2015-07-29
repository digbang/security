<?php namespace Digbang\Security\Events;

use Digbang\Security\Configurations\Configuration;

final class SecurityContextEvent
{
	/**
	 * @type Configuration
	 */
	private $configuration;

	/**
	 * @type string
	 */
	private $context;

	/**
	 * SecurityContextEvent constructor.
	 *
	 * @param Configuration $configuration
	 * @param string        $context
	 */
	public function __construct(Configuration $configuration, $context)
	{
		$this->configuration = $configuration;
		$this->context = $context;
	}

	/**
	 * @return Configuration
	 */
	public function getConfiguration()
	{
		return $this->configuration;
	}

	/**
	 * @return string
	 */
	public function getContext()
	{
		return $this->context;
	}
}
