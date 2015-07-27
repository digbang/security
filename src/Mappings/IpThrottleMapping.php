<?php namespace Digbang\Security\Mappings;

use Digbang\Doctrine\Metadata\Builder;
use Digbang\Doctrine\Metadata\EntityMapping;
use Digbang\Security\Throttling\IpThrottle;
use Digbang\Security\Throttling\IpThrottleMappingTrait;

final class IpThrottleMapping implements EntityMapping
{
	use IpThrottleMappingTrait;

	/**
	 * Returns the fully qualified name of the entity that this mapper maps.
	 *
	 * @return string
	 */
	public function getEntityName()
	{
		return IpThrottle::class;
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
