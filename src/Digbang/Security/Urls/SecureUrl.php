<?php namespace Digbang\Security\Urls;

use Cartalyst\Sentry\Sentry;
use Digbang\Security\Permissions\PermissionRepository;
use Digbang\Security\Permissions\Exceptions\PermissionException;
use Illuminate\Routing\UrlGenerator;

/**
 * Class SecureUrl
 * @package Digbang\Security\Urls
 */
class SecureUrl
{
	protected $sentry;
	protected $url;
	protected $permissionRepository;

	public function __construct(UrlGenerator $url, PermissionRepository $permissionRepository, Sentry $sentry)
	{
		$this->url = $url;
		$this->permissionRepository = $permissionRepository;
		$this->sentry = $sentry;
	}

	/**
	 * @param string $route
	 * @param array  $parameters
	 *
	 * @return string
	 * @throws \Digbang\Security\Permissions\Exceptions\PermissionException
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
	 * @throws \Digbang\Security\Permissions\Exceptions\PermissionException
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
	 * @param string $path
	 * @param array  $extra
	 * @param null   $secure
	 *
	 * @return string
	 * @throws \Digbang\Security\Permissions\Exceptions\PermissionException
	 */
	public function may($path, $extra = array(), $secure = null)
	{
		$permission = $this->permissionRepository->getForPath($path);

	    if (! $this->hasPermission($permission))
	    {
		    throw new PermissionException("Current user does not have required permission: $permission");
	    }

	    return $this->url->to($path, $extra, $secure);
	}

	protected function hasPermission($permission)
	{
		if (!$permission)
		{
			return true;
		}

		if (! $user = $this->sentry->getUser())
		{
			return false;
		}

		return $user->hasAccess($permission);
	}

	/**
	 * Allow access to the URL object
	 * @return UrlGenerator
	 */
	public function insecure()
	{
		return $this->url;
	}

	public function best($method, array $routes)
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

			try {
				return call_user_func_array([$this, $method], $route);
			} catch (PermissionException $e){}
		}
	}

    public function bestRoute(array $routes)
    {
	    return $this->best('route', $routes);
    }

	public function bestAction(array $actions)
	{
		return $this->best('action', $actions);
	}
}
