<?php
namespace Digbang\Security\Mappings;

use Digbang\Security\Throttling\DefaultIpThrottle;
use Digbang\Security\Throttling\IpThrottleMappingTrait;
use LaravelDoctrine\Fluent\EntityMapping;
use LaravelDoctrine\Fluent\Fluent;

final class IpThrottleMapping extends EntityMapping
{
	use IpThrottleMappingTrait;

	/**
	 * Returns the fully qualified name of the class that this mapper maps.
	 *
	 * @return string
	 */
	public function mapFor()
	{
		return DefaultIpThrottle::class;
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
