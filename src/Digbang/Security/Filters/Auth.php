<?php namespace Digbang\Security\Filters;

use Digbang\Security\Auth\AccessControl;
use Digbang\Security\Urls\SecureUrl;
use Illuminate\Routing\Redirector;
use Illuminate\Routing\Route;

class Auth
{
	/**
	 * @var \Illuminate\Routing\Redirector
	 */
	protected $redirector;

	/**
	 * @var \Digbang\Security\Auth\AccessControl
	 */
	protected $accessControl;

	/**
	 * @var \Digbang\Security\Urls\SecureUrl
	 */
	protected $secureUrl;

	public function __construct(Redirector $redirector, AccessControl $accessControl, SecureUrl $secureUrl)
	{
		$this->redirector    = $redirector;
		$this->accessControl = $accessControl;
		$this->secureUrl     = $secureUrl;
	}

	public function logged()
	{
		if (!$this->accessControl->isLogged())
		{
			return $this->redirector->guest($this->secureUrl->route('backoffice.auth.login'));
		}
	}

	public function withPermissions(Route $route)
	{
		if ($redirect = $this->logged())
		{
			return $redirect;
		}

		// Try to make the route, and let it explode upwards
		$this->secureUrl->action($route->getActionName());
	}
}
