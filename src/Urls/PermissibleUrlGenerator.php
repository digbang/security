<?php namespace Digbang\Security\Urls;

use Illuminate\Contracts\Routing\UrlGenerator;

/**
 * @package Digbang\Security\Urls
 */
interface PermissibleUrlGenerator extends UrlGenerator
{
	/**
	 * Allow access to the UrlGenerator object without checking permissions.
	 *
	 * @return UrlGenerator
	 */
	public function insecure();

	/**
	 * Try each route in order, return the first one that the
	 * current user has permission to access.
	 *
	 * @param array $routes
	 *
	 * @return string|null
	 */
	public function bestRoute(array $routes);

	/**
	 * Try each action in order, return the first one that the
	 * current user has permission to access.
	 *
	 * @param array $actions
	 *
	 * @return string|null
	 */
	public function bestAction(array $actions);

	/**
	 * Try each path in order, return the first one that the
	 * current user has permission to access.
	 *
	 * @param array $paths
	 *
	 * @return string|null
	 */
	public function bestPath(array $paths);
}