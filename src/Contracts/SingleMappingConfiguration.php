<?php namespace Digbang\Security\Contracts;

use Digbang\Doctrine\Metadata\EntityMapping;

interface SingleMappingConfiguration extends Configuration
{
	/**
	 * @param EntityMapping $entityMapping
	 */
	public function setMapping(EntityMapping $entityMapping);

	/**
	 * @return EntityMapping|string
	 */
	public function getMapping();
}
