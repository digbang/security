<?php
namespace Digbang\Security\Mappings;

use Digbang\Security\Throttling\DefaultThrottle;
use Digbang\Security\Throttling\ThrottleMappingTrait;
use LaravelDoctrine\Fluent\EntityMapping;
use LaravelDoctrine\Fluent\Fluent;

final class ThrottleMapping extends EntityMapping implements CustomTableMapping
{
	use ThrottleMappingTrait;

	/**
	 * @var string
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
	 * Set the custom table name.
	 *
	 * @param string $table
	 */
	public function setTable($table)
	{
		$this->table = $table;
	}

	/**
	 * Returns the fully qualified name of the class that this mapper maps.
	 *
	 * @return string
	 */
	public function mapFor()
	{
		return DefaultThrottle::class;
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

		$this->addMappings($builder);
	}
}
