<?php namespace Digbang\Security\Entities;

use Digbang\Security\Contracts\Role as GroupInterface;

trait GroupPermissionTrait
{
	use PermissionTrait;

	/**
	 * @type GroupInterface
	 */
	private $group;
}
