<?php namespace Digbang\Security\Permissions;

use Digbang\Security\Permissions\Exceptions\PermissionException;
use Illuminate\Config\Repository;
use Illuminate\Routing\Route;
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
	 * @type \Illuminate\Routing\Router
	 */
	protected $router;

	/**
	 * @type string
	 */
	protected $prefix;

	/**
	 * @type \Illuminate\Config\Repository
	 */
	protected $config;

	/**
	 * Flyweight Pattern
	 * @type array
	 */
	protected $routes = [];

	/**
	 * Flyweight Pattern
	 * @type array
	 */
	protected $permissions = [];

	/**
	 * @param Router     $router
	 * @param Repository $config
	 */
	public function __construct(Router $router, Repository $config)
	{
		$this->router = $router;
		$this->config = $config;
	}

	/**
	 * @param  string $routeName
	 * @return string The permission matching the route, if it needs one.
	 */
	public function getForRoute($routeName)
	{
		foreach ($this->getRoutes() as $route)
		{
			/* @type $route \Illuminate\Routing\Route */
			if ($route->getName() == $routeName)
			{
				return $this->extractPermissionFrom($route);
			}
		}
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
			/* @type $route \Illuminate\Routing\Route */
			if ($route->getActionName() == $action)
			{
				return $this->extractPermissionFrom($route);
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
		if (empty($this->permissions))
		{
			foreach ($this->getRoutes() as $route)
			{
				/* @type $route \Illuminate\Routing\Route */
				if ($permission = $this->extractPermissionFrom($route))
				{
					$this->permissions[] = $permission;
				}
			}
		}

		return $this->permissions;
	}

	/**
	 * @return array
	 */
	protected function getRoutes()
	{
		if (empty($this->routes))
		{
			$this->prefix = $this->prefix ?: $this->config->get('security::permissions.prefix');

			foreach ($this->router->getRoutes() as $route)
			{
				/* @type $route \Illuminate\Routing\Route */
				if (empty($this->prefix) || starts_with($route->getName(), $this->prefix))
				{
					$this->routes[] = $route;
				}
			}
		}

		return $this->routes;
	}

	/**
	 * @param Route $route
	 *
	 * @return string|null
	 * @internal
	 */
	public function extractPermissionFrom(Route $route)
	{
		$parameters = $route->getAction();

		if (isset($parameters['permission']))
		{
			return $parameters['permission'];
		}

		return null;
	}
}
