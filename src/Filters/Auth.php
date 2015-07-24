<?php namespace Digbang\Security\Filters;

use Digbang\Security\Auth\AccessControl;
use Digbang\Security\Urls\SecureUrl;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Routing\Route;

final class Auth
{
	/**
	 * @var ResponseFactory
	 */
	private $responseFactory;

	/**
	 * @var AccessControl
	 */
	private $accessControl;

	/**
	 * @var SecureUrl
	 */
	private $secureUrl;

	/**
	 * @type Repository
	 */
	private $config;

	/**
	 * @param ResponseFactory $responseFactory
	 * @param AccessControl   $accessControl
	 * @param SecureUrl       $secureUrl
	 * @param Repository      $config
	 */
	public function __construct(ResponseFactory $responseFactory, AccessControl $accessControl, SecureUrl $secureUrl, Repository $config)
	{
		$this->responseFactory = $responseFactory;
		$this->accessControl   = $accessControl;
		$this->secureUrl       = $secureUrl;
		$this->config          = $config;
	}

	/**
	 * @return \Illuminate\Http\RedirectResponse|null
	 */
	public function logged()
	{
		if (! $this->accessControl->isLogged())
		{
			return $this->responseFactory->redirectGuest(
				$this->secureUrl->insecure()->route(
					$this->config->get('security::auth.login_route')
				)
			);
		}
	}

	/**
	 * @param Route $route
	 * @return \Illuminate\Http\RedirectResponse|null
	 */
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
