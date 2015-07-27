<?php namespace Digbang\Security\Contracts;

use Digbang\Doctrine\Metadata\DecoupledMappingDriver;

interface Configuration
{
	/**
	 * @param string $table
	 */
	public function setTable($table);

	/**
	 * @return string
	 */
	public function getTable();

	/**
	 * @param DecoupledMappingDriver $mappingDriver
	 * @return void
	 */
	public function map(DecoupledMappingDriver $mappingDriver);
}
