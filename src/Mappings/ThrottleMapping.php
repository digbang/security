<?php
namespace Digbang\Security\Mappings;

use Digbang\Security\Throttling\DefaultThrottle;
use Digbang\Security\Throttling\ThrottleMappingTrait;
use LaravelDoctrine\Fluent\Fluent;

final class ThrottleMapping extends CustomTableMapping
{
	use ThrottleMappingTrait;

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
		parent::map($builder);

		$this->addMappings($builder);
	}
}
