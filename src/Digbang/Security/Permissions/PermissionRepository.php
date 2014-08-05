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
	 * @param  string $path
	 * @return string The permission matching the path, if it needs one.
	 */
	public function getForPath($path);

	/**
	 * List all permissions.
	 * @return array
	 */
	public function all();
}