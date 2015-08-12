<?php namespace Digbang\Security\Laravel\Middleware;

use Cartalyst\Sentinel\Activations\ActivationRepositoryInterface;
use Cartalyst\Sentinel\Reminders\ReminderRepositoryInterface;
use Digbang\Security\Configurations\SecurityContextConfiguration;
use Digbang\Security\Security;
use Digbang\Security\SecurityContext;
use Illuminate\Http\Request;
use Psr\Log\LoggerInterface;

final class SecurityMiddleware
{
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
	 * @param SecurityContext $securityContext
	 * @param LoggerInterface $logger
	 */
	public function __construct(SecurityContext $securityContext, LoggerInterface $logger)
	{
		$this->securityContext = $securityContext;
		$this->logger          = $logger;
	}

	/**
     * Run the request filter.
     *
     * @param Request  $request
     * @param \Closure $next
	 * @param string   $context
     * @return mixed
     */
    public function handle(Request $request, \Closure $next, $context)
    {
	    $this->securityContext->bindContext($context, $request);

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
		        $e->getMessage(),
			    $e->getTrace()
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
