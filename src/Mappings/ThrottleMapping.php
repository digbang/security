<?php namespace Digbang\Security\Mappings;

use Digbang\Doctrine\Metadata\Builder;
use Digbang\Doctrine\Metadata\EntityMapping;
use Digbang\Security\Throttling\DefaultThrottle;
use Digbang\Security\Throttling\ThrottleMappingTrait;

final class ThrottleMapping implements EntityMapping, CustomTableMapping
{
	use ThrottleMappingTrait;

	/**
	 * @type string
	 */
	private $table;

	/**
	 * @return string
	 */
	public function getTable()
	{
		return $this->table;
	}

	/**
	 * Returns the fully qualified name of the entity that this mapper maps.
	 *
	 * @return string
	 */
	public function getEntityName()
	{
		return DefaultThrottle::class;
	}

	/**
	 * Load the entity's metadata through the Metadata Builder object.
	 *
	 * @param Builder $builder
	 *
	 * @return void
	 */
	public function build(Builder $builder)
	{
		if ($this->table)
		{
			$builder->table($this->table);
		}

		$this->addMappings($builder);
	}

	/**
	 * Set the custom table name.
	 *
	 * @param string $table
	 */
	public function setTable($table)
	{
		$this->table = $table;
	}
}
