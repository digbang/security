<?php namespace Digbang\Security\Contracts;

interface MultiMappingConfiguration extends Configuration
{
	/**
	 * @param string $key
	 * @param SingleMappingConfiguration $configuration
	 * @return void
	 */
	public function add($key, SingleMappingConfiguration $configuration);

	/**
	 * @param string $key
	 * @return SingleMappingConfiguration
	 */
	public function get($key);
}
