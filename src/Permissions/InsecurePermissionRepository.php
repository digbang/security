<?php namespace Digbang\Security\Permissions;

class InsecurePermissionRepository implements PermissionRepository
{
	public function getForRoute($route) { return null; }

	public function getForAction($action) { return null; }

	public function all()
	{
		return [];
	}
}
