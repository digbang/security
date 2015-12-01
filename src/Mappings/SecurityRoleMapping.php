<?php
namespace Digbang\Security\Mappings;

use LaravelDoctrine\Fluent\EntityMapping;

abstract class SecurityRoleMapping extends EntityMapping implements CustomTableMapping, PermissibleMapping
{
	/**
	 * Change the roles join table name.
	 *
	 * @param string $table
	 */
	abstract public function changeRolesJoinTable($table);
}
