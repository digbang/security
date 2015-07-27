<?php namespace Digbang\Security\Mappings;

use Digbang\Doctrine\Metadata\Builder;
use Digbang\Doctrine\Metadata\EntityMapping;
use Digbang\Security\Permissions\PermissionCollection;
use Digbang\Security\Permissions\PermissionCollectionMappingTrait;

final class PermissionCollectionMapping implements EntityMapping
{
	use PermissionCollectionMappingTrait;

	/**
	 * Returns the fully qualified name of the entity that this mapper maps.
	 *
	 * @return string
	 */
	public function getEntityName()
	{
		return PermissionCollection::class;
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
