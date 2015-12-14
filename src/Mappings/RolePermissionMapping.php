<?php
namespace Digbang\Security\Mappings;

use Digbang\Security\Permissions\DefaultRolePermission;
use Digbang\Security\Permissions\RolePermissionMappingTrait;
use LaravelDoctrine\Fluent\Fluent;

final class RolePermissionMapping extends CustomTableMapping
{
	use RolePermissionMappingTrait;

	/**
	 * Returns the fully qualified name of the class that this mapper maps.
	 *
	 * @return string
	 */
	public function mapFor()
	{
		return DefaultRolePermission::class;
	}

	/**
	 * Load the object's metadata through the Metadata Builder object.
	 *
	 * @param Fluent $builder
	 */
	public function map(Fluent $builder)
	{
		parent::map($builder);

		$this->addMappings($builder);
	}
}
