<?php namespace Digbang\Security\Mappings;

interface CustomTableMapping
{
	/**
	 * Set the custom table name.
	 *
	 * @param string $table
	 */
	public function setTable($table);

	/**
	 * Get the custom table name, or null if it was not customized.
	 *
	 * @return string|null
	 */
	public function getTable();
}