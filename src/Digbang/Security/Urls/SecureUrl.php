<?php namespace Digbang\Security\Urls;

use Cartalyst\Sentry\Users\UserInterface;
use Digbang\Security\Permissions\PermissionRepository;
use Digbang\Security\Permissions\Exceptions\PermissionException;
use Illuminate\Routing\UrlGenerator;

class SecureUrl
{
	protected $user;
	protected $url;
	protected $permissionRepository;

	public function __construct(UrlGenerator $url, PermissionRepository $permissionRepository, UserInterface $user = null)
	{
		$this->url = $url;
		$this->permissionRepository = $permissionRepository;
		$this->user = $user;
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

		if (!$this->user)
		{
			return false;
		}

		return $this->user->hasPermission($permission);
	}
}
