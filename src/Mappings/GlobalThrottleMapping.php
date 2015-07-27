<?php namespace Digbang\Security\Mappings;

use Digbang\Doctrine\Metadata\Builder;
use Digbang\Doctrine\Metadata\EntityMapping;
use Digbang\Security\Throttling\GlobalThrottle;

final class GlobalThrottleMapping implements EntityMapping
{
	/**
	 * Returns the fully qualified name of the entity that this mapper maps.
	 *
	 * @return string
	 */
	public function getEntityName()
	{
		return GlobalThrottle::class;
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
		// Global throttle is an empty implementation of Throttle.
	}
}
