<?php
namespace Digbang\Security\Mappings;

use LaravelDoctrine\Fluent\EntityMapping;

abstract class SecurityUserMapping extends EntityMapping implements CustomTableMapping, PermissibleMapping
{
	/**
	 * Disable the roles relation.
	 * @return void
	 */
	abstract public function disableRoles();

	/**
	 * Disable the throttles relation.
	 * @return void
	 */
	abstract public function disableThrottles();

	/**
	 * Change the roles join table name.
	 *
	 * @param string $table
	 */
	abstract public function changeRolesJoinTable($table);
}
