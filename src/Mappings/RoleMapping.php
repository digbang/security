<?php
namespace Digbang\Security\Mappings;

use Digbang\Security\Roles\DefaultRole;
use Digbang\Security\Roles\RoleMappingTrait;
use LaravelDoctrine\Fluent\Fluent;

final class RoleMapping extends SecurityRoleMapping
{
	use RoleMappingTrait;

	/**
	 * @var string
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
	 * @return string
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
		return DefaultRole::class;
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
