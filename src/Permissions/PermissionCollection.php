<?php namespace Digbang\Security\Permissions;

use Cartalyst\Sentinel\Permissions\PermissionsInterface;
use Illuminate\Support\Collection;

class PermissionCollection extends Collection
{
	private $encoded;

	public function __construct(array $permissions = [])
	{
		parent::__construct($permissions);
	}

	public function onPrePersist()
	{
		$this->encode();
	}
}
