<?php namespace Digbang\Security\Urls;

use Cartalyst\Sentry\Users\UserInterface;
use Digbang\Security\Exceptions\PermissionException;
use Illuminate\Routing\UrlGenerator;

class SecureUrl
{
	protected $user;
	protected $url;

	public function __construct(UserInterface $user, UrlGenerator $url)
	{
		$this->user = $user;
		$this->url = $url;
	}

	/**
	 * @param string $route
	 * @param string $permission
	 * @param array  $parameters
	 *
	 * @return string
	 * @throws \Digbang\Security\Exceptions\PermissionException
	 */
	public function route($route, $permission, $parameters = [])
	{
	    if (! $this->user->hasPermission($permission))
	    {
		    throw new PermissionException("Current user does not have required permission: $permission");
	    }

	    return $this->url->route($route, $parameters);
	}

	/**
	 * @param string $action
	 * @param string $permission
	 * @param array  $parameters
	 *
	 * @return string
	 * @throws \Digbang\Security\Exceptions\PermissionException
	 */
	public function action($action, $permission, $parameters = [])
	{
	    if (! $this->user->hasPermission($permission))
	    {
		    throw new PermissionException("Current user does not have required permission: $permission");
	    }

	    return $this->url->action($action, $parameters);
	}

	/**
	 * @param string $path
	 * @param string $permission
	 * @param array  $extra
	 * @param null   $secure
	 *
	 * @return string
	 * @throws \Digbang\Security\Exceptions\PermissionException
	 */
	public function may($path, $permission, $extra = array(), $secure = null)
	{
	    if (! $this->user->hasPermission($permission))
	    {
		    throw new PermissionException("Current user does not have required permission: $permission");
	    }

	    return $this->url->to($path, $extra, $secure);
	}
}
