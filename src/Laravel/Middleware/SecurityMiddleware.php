<?php namespace Digbang\Security\Laravel\Middleware;

use Cartalyst\Sentinel\Activations\ActivationRepositoryInterface;
use Cartalyst\Sentinel\Reminders\ReminderRepositoryInterface;
use Digbang\Security\Configurations\SecurityContextConfiguration;
use Digbang\Security\Contracts\SecurityApi;
use Digbang\Security\Security;
use Digbang\Security\SecurityContext;
use Illuminate\Contracts\Container\Container;
use Psr\Log\LoggerInterface;

final class SecurityMiddleware
{
	/**
	 * @type Container
	 */
	private $container;

	/**
	 * @type SecurityContext
	 */
	private $securityContext;

	/**
	 * @type LoggerInterface
	 */
	private $logger;

	/**
	 * SecurityContext constructor.
	 *
	 * @param Container       $container
	 * @param SecurityContext $securityContext
	 * @param LoggerInterface $logger
	 */
	public function __construct(Container $container, SecurityContext $securityContext, LoggerInterface $logger)
	{
		$this->container       = $container;
		$this->securityContext = $securityContext;
		$this->logger          = $logger;
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
	    $this->container->bind(SecurityApi::class, function() use ($context){
		    return $this->securityContext->getSecurity($context);
	    });

	    $request->setUserResolver(function() use ($context){
            return $this->securityContext->getSecurity($context)->getUser();
        });

	    $response = $next($request);

	    $this->garbageCollect(
		    $this->securityContext->getSecurity($context),
		    $this->securityContext->getConfigurationFor($context)
	    );

	    return $response;
    }

	/**
	 * Garbage collect activations and reminders.
	 *
	 * @param Security                     $security
	 * @param SecurityContextConfiguration $configuration
	 */
    protected function garbageCollect(Security $security, SecurityContextConfiguration $configuration)
    {
	    try
	    {
		    $activations = $security->activations();
		    $reminders   = $security->reminders();

            $this->sweep($activations, $configuration->getActivationsLottery());
            $this->sweep($reminders,   $configuration->getRemindersLottery());
	    }
	    catch (\Exception $e)
	    {
		    // Silently fail and report, but still serve the content.
		    $this->logger->error(
			    "Unable to garbage collect reminders or activations: " .
		        $e->getMessage() . PHP_EOL . $e->getTraceAsString()
		    );
	    }
    }

	/**
     * Sweep expired codes.
     *
     * @param  ReminderRepositoryInterface|ActivationRepositoryInterface $repository
     * @param  array  $lottery
     * @return void
     */
    protected function sweep($repository, $lottery)
    {
        if ($this->hitsLottery($lottery))
        {
            $repository->removeExpired();
        }
    }

	/**
     * Determine if the configuration odds hit the lottery.
     *
     * @param  array  $lottery
     * @return bool
     */
    protected function hitsLottery(array $lottery)
    {
        return mt_rand(1, $lottery[1]) <= $lottery[0];
    }
}
