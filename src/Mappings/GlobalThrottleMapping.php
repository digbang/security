<?php
namespace Digbang\Security\Mappings;

use Digbang\Security\Throttling\DefaultGlobalThrottle;
use LaravelDoctrine\Fluent\EntityMapping;
use LaravelDoctrine\Fluent\Fluent;

final class GlobalThrottleMapping extends EntityMapping
{
	/**
	 * Returns the fully qualified name of the class that this mapper maps.
	 *
	 * @return string
	 */
	public function mapFor()
	{
		return DefaultGlobalThrottle::class;
	}

	/**
	 * Load the object's metadata through the Metadata Builder object.
	 *
	 * @param Fluent $builder
	 */
	public function map(Fluent $builder)
	{
		// Global throttle is an empty implementation of Throttle.
	}
}
