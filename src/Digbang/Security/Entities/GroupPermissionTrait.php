<?php namespace Digbang\Security\Entities;

use Digbang\Security\Contracts\Group as GroupInterface;

trait GroupPermissionTrait
{
	use PermissionTrait;

	/**
	 * @type GroupInterface
	 */
	private $group;
}
