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
}
