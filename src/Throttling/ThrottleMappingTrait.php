<?php
namespace Digbang\Security\Throttling;

use LaravelDoctrine\Fluent\Fluent;

trait ThrottleMappingTrait
{
	public function addMappings(Fluent $builder)
	{
		$builder->bigIncrements('id');
		$builder->singleTableInheritance()->column('type');
		$builder->carbonDateTime('createdAt');
		$builder->carbonDateTime('updatedAt');

		$builder->events()
			->prePersist('onPrePersist')
			->preUpdate('onPreUpdate');
	}
}
