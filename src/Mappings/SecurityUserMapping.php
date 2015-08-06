<?php namespace Digbang\Security\Mappings;

use Digbang\Doctrine\Metadata\EntityMapping;
use Digbang\Security\Mappings\CustomTableMapping;

interface SecurityUserMapping extends EntityMapping, CustomTableMapping, PermissibleMapping
{
	/**
	 * Disable the roles relation.
	 * @return void
	 */
	public function disableRoles();

	/**
	 * Disable the throttles relation.
	 * @return void
	 */
	public function disableThrottles();

	/**
	 * Change the roles join table name.
	 *
	 * @param string $table
	 */
	public function changeRolesJoinTable($table);
}
