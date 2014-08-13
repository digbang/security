<?php namespace Digbang\Security\Filters;

use Cartalyst\Sentry\Sentry;
use Digbang\Security\Urls\SecureUrl;
use Illuminate\Routing\Redirector;
use Illuminate\Routing\Route;

class Auth
{
	protected $redirector;
	protected $sentry;
	protected $secureUrl;

	public function __construct(Redirector $redirector, Sentry $sentry, SecureUrl $secureUrl)
	{
		$this->redirector   = $redirector;
		$this->sentry       = $sentry;
		$this->secureUrl = $secureUrl;
	}

	public function logged()
	{
		if (!$this->sentry->check())
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
