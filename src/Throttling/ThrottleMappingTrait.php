<?php namespace Digbang\Security\Throttling;

use Digbang\Doctrine\Metadata\Builder;

trait ThrottleMappingTrait
{
	public function addMappings(Builder $builder)
	{
		$builder
			->primary()
			->inheritance('type')
			->nullableString('ip')
			->timestamps();
	}
}
