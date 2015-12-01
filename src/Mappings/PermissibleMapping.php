<?php
namespace Digbang\Security\Mappings;

interface PermissibleMapping
{
	/**
	 * Disable the permissions relation.
	 * @return void
	 */
	public function disablePermissions();
}
