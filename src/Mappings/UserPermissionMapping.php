<?php
namespace Digbang\Security\Mappings;

use Digbang\Security\Permissions\DefaultUserPermission;
use Digbang\Security\Permissions\UserPermissionMappingTrait;
use LaravelDoctrine\Fluent\EntityMapping;
use LaravelDoctrine\Fluent\Fluent;

final class UserPermissionMapping extends EntityMapping implements CustomTableMapping
{
	use UserPermissionMappingTrait;

	/**
	 * @type string
	 */
	private $table;

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

	/**
	 * Returns the fully qualified name of the class that this mapper maps.
	 *
	 * @return string
	 */
	public function mapFor()
	{
		return DefaultUserPermission::class;
	}

	/**
	 * Load the object's metadata through the Metadata Builder object.
	 *
	 * @param Fluent $builder
	 */
	public function map(Fluent $builder)
	{
		if ($this->table)
		{
			$builder->table($this->table);
		}

		$this->addMappings($builder);
	}
}
