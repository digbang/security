<?php namespace Digbang\Security\Permissions;
use Digbang\Security\Permissions\Exceptions\PermissionException;
use Illuminate\Routing\Router;

/**
 * Class RoutePermissionRepository
 * By using this class, you expect every url to be secured.
 * Permissions required for each url will have the same name as the route,
 * usually backoffice.{resource}.{method}
 * @package Digbang\Security\Permissions
 */
class RoutePermissionRepository implements PermissionRepository
{
	protected $router;

	function __construct(Router $router)
	{
		$this->router = $router;
	}

	/**
	 * @param  string $route
	 * @return string The permission matching the route, if it needs one.
	 */
	public function getForRoute($route)
	{
		return $route;
	}

	/**
	 * @param  string $action
	 *
	 * @return string The permission matching the action, if it needs one.
	 */
	public function getForAction($action)
	{
		foreach ($this->router->getRoutes() as $route)
		{
			/* @var $route \Illuminate\Routing\Route */
			if ($route->getActionName() == $action)
			{
				return $route->getName();
			}
		}
	}

	/**
	 * @param  string $path
	 *
	 * @throws Exceptions\PermissionException
	 * @return string The permission matching the path, if it needs one.
	 */
	public function getForPath($path)
	{
		throw new PermissionException("Path permissions are not implemented in Route permission mode");
	}

	/**
	 * List all permissions.
	 * @return array
	 */
	public function all()
	{
		$routes = [];

		foreach ($this->router->getRoutes() as $route)
		{
			/* @var $route \Illuminate\Routing\Route */
			if ($routeName = $route->getName())
			{
				$routes[] = $routeName;
			}
		}

		return $routes;
	}
}
