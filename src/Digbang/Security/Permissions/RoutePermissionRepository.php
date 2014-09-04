<?php namespace Digbang\Security\Permissions;
use Digbang\Security\Permissions\Exceptions\PermissionException;
use Illuminate\Config\Repository;
use Illuminate\Routing\Router;

/**
 * Class RoutePermissionRepository
 * By using this class, you expect every url to be secured.
 * Permissions required for each url will have the same name as the route,
 * usually {prefix}.{resource}.{method}
 * Remember to set the default prefix through the security configuration file.
 * @package Digbang\Security\Permissions
 */
class RoutePermissionRepository implements PermissionRepository
{
	/**
	 * @var \Illuminate\Routing\Router
	 */
	protected $router;

	/**
	 * @var string
	 */
	protected $prefix;

	/**
	 * Flyweight Pattern
	 * @var array
	 */
	protected $routes = [];

	function __construct(Router $router, Repository $config)
	{
		$this->router = $router;

		$this->prefix = $config->get('security::permissions.prefix');
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
		foreach ($this->getRoutes() as $route)
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

		foreach ($this->getRoutes() as $route)
		{
			/* @var $route \Illuminate\Routing\Route */
			if ($routeName = $route->getName())
			{
				$routes[] = $routeName;
			}
		}

		return $routes;
	}

	protected function getRoutes()
	{
		if (empty($this->routes))
		{
			foreach ($this->router->getRoutes() as $route)
			{
				/* @var $route \Illuminate\Routing\Route */
				if (empty($this->prefix) || starts_with($route->getName(), $this->prefix))
				{
					$this->routes[] = $route;
				}
			}
		}

		return $this->routes;
	}
}
