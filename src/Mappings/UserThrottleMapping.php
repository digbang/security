<?php
namespace Digbang\Security\Mappings;

use Digbang\Security\Throttling\DefaultUserThrottle;
use Digbang\Security\Throttling\UserThrottleMappingTrait;
use LaravelDoctrine\Fluent\EntityMapping;
use LaravelDoctrine\Fluent\Fluent;

final class UserThrottleMapping extends EntityMapping
{
	use UserThrottleMappingTrait;

	/**
	 * Returns the fully qualified name of the class that this mapper maps.
	 *
	 * @return string
	 */
	public function mapFor()
	{
		return DefaultUserThrottle::class;
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
