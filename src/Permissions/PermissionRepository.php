<?php namespace Digbang\Security\Permissions;

interface PermissionRepository
{
	/**
	 * @param  string $route
	 * @return string The permission matching the route, if it needs one.
	 */
	public function getForRoute($route);

	/**
	 * @param  string $action
	 * @return string The permission matching the action, if it needs one.
	 */
	public function getForAction($action);

	/**
	 * List all permissions.
	 * @return array
	 */
	public function all();
}