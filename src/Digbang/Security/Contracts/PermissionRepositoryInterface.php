<?php namespace Digbang\Security\Contracts;

interface PermissionRepositoryInterface
{
	/**
	 * @param  string $route
	 * @return string The permission matching the url, if it needs one.
	 */
	public function getForRoute($route);

	/**
	 * @param  string $action
	 * @return string The permission matching the url, if it needs one.
	 */
	public function getForAction($action);

	/**
	 * @param  string $path
	 * @return string The permission matching the url, if it needs one.
	 */
	public function getForPath($path);
}