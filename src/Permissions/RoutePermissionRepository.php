<?php namespace Digbang\Security\Permissions;

use Illuminate\Routing\Route;
use Illuminate\Routing\Router;

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
	 * @param Router $router
	 */
	public function __construct(Router $router)
	{
		$this->router = $router;
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

		return null;
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
			foreach ($this->router->getRoutes() as $route)
			{
				/* @type $route \Illuminate\Routing\Route */
				$this->routes[] = $route;
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
