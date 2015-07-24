<?php namespace Digbang\Security\Permissions;

use Digbang\Doctrine\Metadata\Builder;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\Builder\FieldBuilder;

trait PermissionCollectionMappingTrait
{
	public function addMappings(Builder $builder)
	{
		$builder
			->embeddable()
			->nullableText('encoded', function(FieldBuilder $fieldBuilder){
				$fieldBuilder->columnName('permissions');
			})
			->addLifecycleEvent('onPrePersist', Events::prePersist);
	}
}
