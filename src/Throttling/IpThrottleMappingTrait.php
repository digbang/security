<?php namespace Digbang\Security\Throttling;

use Digbang\Doctrine\Metadata\Builder;

trait IpThrottleMappingTrait
{
	public function addMappings(Builder $builder)
	{
		$builder->nullableString('ip');
	}
}
