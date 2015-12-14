<?php
namespace Digbang\Security\Mappings;

use Digbang\Security\Activations\DefaultActivation;
use Digbang\Security\Activations\ActivationMappingTrait;
use LaravelDoctrine\Fluent\EntityMapping;
use LaravelDoctrine\Fluent\Fluent;

final class ActivationMapping extends EntityMapping implements  CustomTableMapping
{
	use ActivationMappingTrait;

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
		return DefaultActivation::class;
	}

	/**
	 * Load the object's metadata through the Metadata Builder object.
	 *
	 * @param Fluent $builder
	 */
	public function map(Fluent $builder)
	{
		$this->addMappings($builder);
	}
}
