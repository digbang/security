<?php namespace Digbang\Security\Mappings;

use Digbang\Doctrine\Metadata\Builder;
use Digbang\Doctrine\Metadata\EntityMapping;
use Digbang\Security\Activations\Activation;
use Digbang\Security\Activations\ActivationMappingTrait;

final class ActivationMapping implements EntityMapping, CustomTableMapping
{
	use ActivationMappingTrait;

	/**
	 * @type string
	 */
	private $table;

	/**
	 * @param string $table
	 */
	public function setTable($table)
	{
		$this->table = $table;
	}

	/**
	 * Returns the fully qualified name of the entity that this mapper maps.
	 *
	 * @return string
	 */
	public function getEntityName()
	{
		return Activation::class;
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
}
