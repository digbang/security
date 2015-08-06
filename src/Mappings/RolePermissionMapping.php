<?php namespace Digbang\Security\Mappings;

use Digbang\Doctrine\Metadata\Builder;
use Digbang\Doctrine\Metadata\EntityMapping;
use Digbang\Security\Permissions\DefaultRolePermission;
use Digbang\Security\Permissions\RolePermissionMappingTrait;

final class RolePermissionMapping implements EntityMapping, CustomTableMapping
{
	use RolePermissionMappingTrait;

	private $table;

	/**
	 * Returns the fully qualified name of the entity that this mapper maps.
	 *
	 * @return string
	 */
	public function getEntityName()
	{
		return DefaultRolePermission::class;
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
		if ($this->table)
		{
			$builder->table($this->table);
		}

		$this->addMappings($builder);
	}

	/**
	 * Set the custom table name.
	 *
	 * @param string $table
	 */
	public function setTable($table)
	{
		$this->table = $table;
	}

	/**
	 * Get the custom table name, or null if it was not customized.
	 *
	 * @return string|null
	 */
	public function getTable()
	{
		return $this->table;
	}
}
