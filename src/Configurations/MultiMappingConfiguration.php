<?php namespace Digbang\Security\Configurations;

use Digbang\Doctrine\Metadata\DecoupledMappingDriver;
use Digbang\Security\Contracts;
use Digbang\Security\Contracts\SingleMappingConfiguration;
use Illuminate\Contracts\Container\Container;

final class MultiMappingConfiguration implements Contracts\MultiMappingConfiguration
{
	use ConfigurationTrait;

	/**
	 * @type Container
	 */
	private $container;

	/**
	 * @type array
	 */
	private $configurations = [];

	/**
	 * ThrottleConfiguration constructor.
	 *
	 * @param $container
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	public function map(DecoupledMappingDriver $mappingDriver)
	{
		foreach ($this->configurations as $configuration)
		{
			/** @type Contracts\SingleMappingConfiguration $configuration */
			$configuration->map($mappingDriver);
		}
	}

	/**
	 * @param string $key
	 * @param SingleMappingConfiguration $configuration
	 * @return void
	 */
	public function add($key, SingleMappingConfiguration $configuration)
	{
		$this->configurations[$key] = $configuration;
	}

	/**
	 * @param string $key
	 * @return SingleMappingConfiguration
	 */
	public function get($key)
	{
		return $this->configurations[$key];
	}
}
