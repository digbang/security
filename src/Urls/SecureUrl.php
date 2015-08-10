<?php namespace Digbang\Security\Urls;

use Digbang\Security\Contracts\SecurityApi;
use Digbang\Security\Permissions\Permissible;
use Digbang\Security\Permissions\PermissionRepository;
use Digbang\Security\Permissions\PermissionException;
use Illuminate\Routing\UrlGenerator;

/**
 * Class SecureUrl
 * @package Digbang\Security\Urls
 */
class SecureUrl
{
	/**
	 * @type UrlGenerator
	 */
	protected $url;

	/**
	 * @type PermissionRepository
	 */
	protected $permissionRepository;
	/**
	 * @type SecurityApi
	 */
	private $securityApi;

	/**
	 * @param UrlGenerator         $url
	 * @param PermissionRepository $permissionRepository
	 * @param SecurityApi          $securityApi
	 */
	public function __construct(UrlGenerator $url, PermissionRepository $permissionRepository, SecurityApi $securityApi)
	{
		$this->url                  = $url;
		$this->permissionRepository = $permissionRepository;
		$this->securityApi          = $securityApi;
	}

	/**
	 * @param string $route
	 * @param array  $parameters
	 *
	 * @return string
	 * @throws PermissionException
	 */
	public function route($route, $parameters = [])
	{
		$permission = $this->permissionRepository->getForRoute($route);

	    if (! $this->hasPermission($permission))
	    {
		    throw new PermissionException("Current user does not have required permission: $permission");
	    }

	    return $this->url->route($route, $parameters);
	}

	/**
	 * @param string $action
	 * @param array  $parameters
	 *
	 * @return string
	 * @throws PermissionException
	 */
	public function action($action, $parameters = [])
	{
		$permission = $this->permissionRepository->getForAction($action);

	    if (! $this->hasPermission($permission))
	    {
		    throw new PermissionException("Current user does not have required permission: $permission");
	    }

	    return $this->url->action($action, $parameters);
	}

	/**
	 * Allow access to the URL object
	 * @return UrlGenerator
	 */
	public function insecure()
	{
		return $this->url;
	}

	/**
	 * Try each route in order, return the first one that the
	 * current user has permission to access.
	 * @param array $routes
	 * @return string|null
	 */
    public function bestRoute(array $routes)
    {
	    return $this->best('route', $routes);
    }

	/**
	 * Try each action in order, return the first one that the
	 * current user has permission to access.
	 *
	 * @param array $actions
	 * @return string|null
	 */
	public function bestAction(array $actions)
	{
		return $this->best('action', $actions);
	}

	/**
	 * Check if the logged user has access to the given permission(s).
	 * Users must implement the Digbang\Security\Permissions\Permissible interface.
	 *
	 * @param string|array $permission
	 * @return bool
	 */
	protected function hasPermission($permission)
	{
		if (!$permission)
		{
			return true;
		}

		if (! $user = $this->securityApi->getUser())
		{
			return false;
		}

		if ($user instanceof Permissible)
		{
			return $user->hasAccess($permission);
		}

		return false;
	}

	/**
	 * @param string $method
	 * @param array $routes
	 *
	 * @return string|null
	 * @throws \UnexpectedValueException
	 */
	protected function best($method, array $routes)
	{
		if (!method_exists($this, $method))
		{
			throw new \UnexpectedValueException("Method $method does not exist.");
		}

		foreach ($routes as $route)
		{
			if (! is_array($route))
			{
				$route = [$route];
			}

			try
			{
				return call_user_func_array([$this, $method], $route);
			}
			catch (PermissionException $e)
			{
				// Do nothing
			}
		}
	}
}
