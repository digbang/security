<?php namespace Digbang\Security\Mappings;

use Digbang\Doctrine\Metadata\Builder;
use Digbang\Doctrine\Metadata\EntityMapping;
use Digbang\Security\Throttling\DefaultUserThrottle;
use Digbang\Security\Throttling\UserThrottleMappingTrait;

final class UserThrottleMapping implements EntityMapping
{
	use UserThrottleMappingTrait;
	/**
	 * Returns the fully qualified name of the entity that this mapper maps.
	 *
	 * @return string
	 */
	public function getEntityName()
	{
		return DefaultUserThrottle::class;
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
		$this->addMappings($builder);
	}
}
