<?php namespace Digbang\Security\Configurations;

use Digbang\Doctrine\Metadata\DecoupledMappingDriver;
use Digbang\Doctrine\Metadata\EntityMapping;

final class EmbeddableConfiguration extends SingleMappingConfiguration
{
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

		$mappingDriver->addEmbeddableMapping($this->mapping);
	}
}
