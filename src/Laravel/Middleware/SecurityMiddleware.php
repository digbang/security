<?php namespace Digbang\Security\Laravel\Middleware;

use Digbang\Security\Configurations\Configuration;
use Digbang\Security\Contracts\SecurityApi;
use Digbang\Security\Events\SecurityContextEvent;
use Digbang\Security\Factories\SecurityFactory;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;

class SecurityMiddleware
{
	/**
	 * @type Container
	 */
	private $container;

	/**
	 * @type SecurityFactory
	 */
	private $securityFactory;
	/**
	 * @type Dispatcher
	 */
	private $events;

	/**
	 * SecurityContext constructor.
	 *
	 * @param Container       $container
	 * @param SecurityFactory $securityFactory
	 * @param Dispatcher      $events
	 */
	public function __construct(Container $container, SecurityFactory $securityFactory, Dispatcher $events)
	{
		$this->container = $container;
		$this->securityFactory = $securityFactory;
		$this->events = $events;
	}

	/**
     * Run the request filter.
     *
     * @param \Illuminate\Http\Request  $request
     * @param \Closure                  $next
	 * @param string                    $context
     * @return mixed
     */
    public function handle($request, \Closure $next, $context)
    {
	    $this->container->bind(SecurityApi::class, function(Container $app) use ($context){
		    /** @type Configuration $configuration */
		    $configuration = $app->make(Configuration::class);

		    $this->events->fire(new SecurityContextEvent($configuration, $context));

		    return $this->securityFactory->fromConfiguration($configuration, $context);
	    });

	    return $next($request);
    }
}
