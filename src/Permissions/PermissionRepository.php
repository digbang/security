<?php namespace Digbang\Security\Permissions;

interface PermissionRepository
{
	/**
	 * @param  string $route
	 * @return string|null The permission matching the route, if it needs one.
	 */
	public function getForRoute($route);

	/**
	 * @param  string $action
	 * @return string|null The permission matching the action, if it needs one.
	 */
	public function getForAction($action);

	/**
	 * @param string $path
	 * @return string|null The permission matching the action, if it needs one.
	 */
	public function getForPath($path);

	/**
	 * List all permissions.
	 * @return array
	 */
	public function all();
}