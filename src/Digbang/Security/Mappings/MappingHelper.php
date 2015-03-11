<?php namespace Digbang\Security\Mappings;

use Digbang\Doctrine\Metadata\Relations\BelongsTo;
use Digbang\Doctrine\Metadata\Relations\Relation;
use Doctrine\ORM\Mapping\Builder\AssociationBuilder;

trait MappingHelper
{
	/**
	 * @param BelongsTo $belongsTo
	 */
	protected function foreignIdentityHack(BelongsTo $belongsTo)
	{
		// Hack to allow ids in foreign keys
		$associationBuilder = $belongsTo->getAssociationBuilder();

		$mapping = $this->getInternalMapping($associationBuilder);
		$mapping['id'] = true;
		$this->setInternalMapping($mapping, $associationBuilder);
	}

	protected function foreignKey($groupClass)
	{
		return snake_case(str_singular(class_basename($groupClass))) . '_id';
	}

	protected function orphanRemovalHack(Relation $relation)
	{
		// Hack to allow orphan removal
		$associationBuilder = $relation->getAssociationBuilder();

		$mapping = $this->getInternalMapping($associationBuilder);
		$mapping['orphanRemoval'] = true;
		$this->setInternalMapping($mapping, $associationBuilder);
	}

	public function getInternalMapping(AssociationBuilder $associationBuilder)
	{
		$property = $this->getReflectionProperty($associationBuilder);
		return $property->getValue($associationBuilder);
	}

	public function setInternalMapping(array $mapping, AssociationBuilder $associationBuilder)
	{
		$property = $this->getReflectionProperty($associationBuilder);
		$property->setValue($associationBuilder, $mapping);
	}

	/**
	 * @param AssociationBuilder $associationBuilder
	 *
	 * @return \ReflectionProperty
	 */
	protected function getReflectionProperty(AssociationBuilder $associationBuilder)
	{
		$ref      = new \ReflectionObject($associationBuilder);
		$property = $ref->getProperty('mapping');
		$property->setAccessible(true);

		return $property;
	}
}
