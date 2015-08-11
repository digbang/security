<?php namespace Digbang\Security\Urls;

use Digbang\Security\Contracts\SecurityApi;
use Digbang\Security\Permissions\Permissible;
use Digbang\Security\Permissions\PermissionRepository;
use Digbang\Security\Permissions\PermissionException;
use Illuminate\Contracts\Routing\UrlGenerator;

class PermissionAwareUrlGenerator implements PermissibleUrlGenerator
{
	/**
	 * @type UrlGenerator
	 */
	private $url;

	/**
	 * @type PermissionRepository
	 */
	private $permissions;

	/**
	 * @type SecurityApi
	 */
	private $securityApi;

	/**
	 * @param UrlGenerator         $url
	 * @param PermissionRepository $permissions
	 * @param SecurityApi          $securityApi
	 */
	public function __construct(UrlGenerator $url, PermissionRepository $permissions, SecurityApi $securityApi)
	{
		$this->url         = $url;
		$this->permissions = $permissions;
		$this->securityApi = $securityApi;
	}

	/**
	 * {@inheritdoc}
	 */
	public function route($name, $parameters = [], $absolute = true)
	{
		$permission = $this->permissions->getForRoute($name);

	    if (! $this->hasPermission($permission))
	    {
		    throw new PermissionException("Current user does not have required permission: $permission");
	    }

	    return $this->url->route($name, $parameters, $absolute);
	}

	/**
	 * {@inheritdoc}
	 */
	public function action($action, $parameters = [], $absolute = true)
	{
		$permission = $this->permissions->getForAction($action);

	    if (! $this->hasPermission($permission))
	    {
		    throw new PermissionException("Current user does not have required permission: $permission");
	    }

	    return $this->url->action($action, $parameters, $absolute);
	}

	/**
	 * {@inheritdoc}
	 */
	public function insecure()
	{
		return $this->url;
	}

	/**
	 * {@inheritdoc}
	 */
	public function to($path, $extra = [], $secure = null)
	{
		$url = $this->url->to($path, $extra, $secure);

		$permission = $this->permissions->getForPath($url);

	    if (! $this->hasPermission($permission))
	    {
		    throw new PermissionException("Current user does not have required permission: $permission");
	    }

	    return $url;
	}

	/**
	 * {@inheritdoc}
	 */
	public function secure($path, $parameters = [])
	{
		return $this->to($path, $parameters, true);
	}

	/**
	 * {@inheritdoc}
	 */
	public function asset($path, $secure = null)
	{
		// Assets are not subject to permissions.
		return $this->url->asset($path, $secure);
	}

	/**
	 * {@inheritdoc}
	 */
	public function setRootControllerNamespace($rootNamespace)
	{
		$this->url->setRootControllerNamespace($rootNamespace);

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function bestRoute(array $routes)
	{
		return $this->best('route', $routes);
	}

	/**
	 * {@inheritdoc}
	 */
	public function bestAction(array $actions)
	{
		return $this->best('action', $actions);
	}

	/**
	 * {@inheritdoc}
	 */
	public function bestPath(array $paths)
	{
		return $this->best('to', $paths);
	}

	/**
	 * Check if the logged user has access to the given permission(s).
	 * Users must implement the Digbang\Security\Permissions\Permissible interface.
	 *
	 * @param string|array $permission
	 * @return bool
	 */
	private function hasPermission($permission)
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
	private function best($method, array $routes)
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
