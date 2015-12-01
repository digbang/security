<?php
namespace Digbang\Security\Throttling;

use LaravelDoctrine\Fluent\Fluent;

trait IpThrottleMappingTrait
{
	public function addMappings(Fluent $builder)
	{
		$builder->string('ip')->nullable();
	}
}
