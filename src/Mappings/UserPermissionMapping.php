<?php
namespace Digbang\Security\Mappings;

use Digbang\Security\Permissions\DefaultUserPermission;
use Digbang\Security\Permissions\UserPermissionMappingTrait;
use LaravelDoctrine\Fluent\Fluent;

final class UserPermissionMapping extends CustomTableMapping
{
	use UserPermissionMappingTrait;


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
		parent::map($builder);

		$this->addMappings($builder);
	}
}
