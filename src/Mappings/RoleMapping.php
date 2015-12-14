<?php
namespace Digbang\Security\Mappings;

use Digbang\Security\Roles\DefaultRole;
use Digbang\Security\Roles\RoleMappingTrait;
use LaravelDoctrine\Fluent\Fluent;

final class RoleMapping extends SecurityRoleMapping
{
	use RoleMappingTrait;

	/**
	 * Returns the fully qualified name of the class that this mapper maps.
	 *
	 * @return string
	 */
	public function mapFor()
	{
		return DefaultRole::class;
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
