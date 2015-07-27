<?php namespace Digbang\Security\Configurations;

use Digbang\Doctrine\Metadata\DecoupledMappingDriver;
use Digbang\Doctrine\Metadata\EntityMapping;
use Digbang\Security\Contracts;
use Illuminate\Contracts\Container\Container;

class SingleMappingConfiguration implements Contracts\SingleMappingConfiguration
{
	use ConfigurationTrait;

	/**
	 * @type string|EntityMapping
	 */
	protected $mapping;

	/**
	 * @type Container
	 */
	protected $container;

	/**
	 * SingleMappingConfiguration constructor.
	 *
	 * @param Container $container
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	/**
	 * @param DecoupledMappingDriver $mappingDriver
	 *
	 * @return void
	 */
	public function map(DecoupledMappingDriver $mappingDriver)
	{
		if (! $this->mapping instanceof EntityMapping)
		{
			$this->mapping = $this->container->make($this->mapping);
		}

		$mappingDriver->addEntityMapping($this->mapping);
	}

	/**
	 * @param EntityMapping $entityMapping
	 */
	public function setMapping(EntityMapping $entityMapping)
	{
		$this->mapping = $entityMapping;
	}

	/**
	 * @return EntityMapping|string
	 */
	public function getMapping()
	{
		return $this->mapping;
	}
}
