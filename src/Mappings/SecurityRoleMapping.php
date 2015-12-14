<?php
namespace Digbang\Security\Mappings;

abstract class SecurityRoleMapping extends CustomTableMapping implements PermissibleMapping
{
	/**
	 * Change the roles join table name.
	 *
	 * @param string $table
	 */
	abstract public function changeRolesJoinTable($table);
}
