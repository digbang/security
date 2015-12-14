<?php
namespace Digbang\Security\Mappings;

use Digbang\Security\Activations\DefaultActivation;
use Digbang\Security\Activations\ActivationMappingTrait;
use LaravelDoctrine\Fluent\Fluent;

final class ActivationMapping extends CustomTableMapping
{
	use ActivationMappingTrait;

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
		parent::map($builder);

		$this->addMappings($builder);
	}
}
