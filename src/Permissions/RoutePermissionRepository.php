<?php namespace Digbang\Security\Permissions;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;

final class RoutePermissionRepository implements PermissionRepository
{
	/**
	 * @type \Illuminate\Routing\Router
	 */
	private $router;

	/**
	 * Flyweight Pattern
	 * @type array
	 */
	private $permissions = [];

	/**
	 * @param Router $router
	 */
	public function __construct(Router $router)
	{
		$this->router = $router;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getForRoute($routeName)
	{
		if ($route = $this->router->getRoutes()->getByName($routeName))
		{
			return $this->extractPermissionFrom($route);
		}

		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getForAction($action)
	{
		if ($route = $this->router->getRoutes()->getByAction($action))
		{
			return $this->extractPermissionFrom($route);
		}

		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function all()
	{
		if (empty($this->permissions))
		{
			foreach ($this->router->getRoutes() as $route)
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
	 * Extracts the permission configured inside the route action array.
	 *
	 * @param Route $route
	 * @return string|null
	 */
	private function extractPermissionFrom(Route $route)
	{
		$parameters = $route->getAction();

		if (isset($parameters['permission']))
		{
			return $parameters['permission'];
		}

		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getForPath($path)
	{
		/** @type Request $request */
		$request = Request::create($path);

		if ($route = $this->router->getRoutes()->match($request))
		{
			return $this->extractPermissionFrom($route);
		}

		return null;
	}
}
