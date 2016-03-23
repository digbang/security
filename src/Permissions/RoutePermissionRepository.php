<?php namespace Digbang\Security\Permissions;

use Digbang\Security\Urls\RouteCollectionMatcher;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class RoutePermissionRepository implements PermissionRepository
{
	/**
	 * @var \Illuminate\Routing\Router
	 */
	private $router;

	/**
	 * Flyweight Pattern
	 * @var array
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
				/* @var $route \Illuminate\Routing\Route */
				if ($permission = $this->extractPermissionFrom($route))
				{
					$this->permissions[] = $permission;
				}
			}

			$this->permissions = array_unique($this->permissions);
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
		/** @var Request $request */
		$request = Request::create($path);

		try
		{
			$collectionMatcher = new RouteCollectionMatcher($this->router->getRoutes());

			if ($route = $collectionMatcher->getRouteForRequest($request))
			{
				return $this->extractPermissionFrom($route);
			}
		}
		catch (HttpException $e){ }

		return null;
	}
}
