<?php
namespace Digbang\Security\Mappings;

use LaravelDoctrine\Fluent\EntityMapping;
use LaravelDoctrine\Fluent\Fluent;

abstract class CustomTableMapping extends EntityMapping
{
	private $table;

	/**
	 * Set the custom table name.
	 *
	 * @param string $table
	 */
	public function setTable($table)
	{
		$this->table = $table;
	}

	/**
	 * Get the custom table name, or null if it was not customized.
	 *
	 * @return string|null
	 */
	public function getTable()
	{
		return $this->table;
	}

	/**
	 * Load the object's metadata through the Metadata Builder object.
	 *
	 * @param Fluent $builder
	 */
	public function map(Fluent $builder)
	{
		if ($this->table)
		{
			$builder->table($this->table);
		}
	}
}
