<?php namespace Digbang\Security\Mappings;

use Digbang\Doctrine\Metadata\EntityMapping;

interface SecurityRoleMapping extends EntityMapping, CustomTableMapping, PermissibleMapping
{
	/**
	 * Change the roles join table name.
	 *
	 * @param string $table
	 */
	public function changeRolesJoinTable($table);
}
