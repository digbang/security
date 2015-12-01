<?php
namespace Digbang\Security\Throttling;

use LaravelDoctrine\Fluent\Fluent;

trait ThrottleMappingTrait
{
	public function addMappings(Fluent $builder)
	{
		$builder->bigIncrements('id');
		$builder->singleTableInheritance()->column('type');
	}
}
