<?php
namespace Digbang\Security\Laravel\Middleware;

use Cartalyst\Sentinel\Activations\ActivationRepositoryInterface;
use Cartalyst\Sentinel\Reminders\ReminderRepositoryInterface;
use Digbang\Security\Configurations\SecurityContextConfiguration;
use Digbang\Security\Contracts\SecurityApi;
use Digbang\Security\Exceptions\Unauthenticated;
use Digbang\Security\Exceptions\Unauthorized;
use Digbang\Security\SecurityContext;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Routing\Route;
use Psr\Log\LoggerInterface;

final class SecurityMiddleware
{
    /**
     * @var SecurityContext
     */
    private $securityContext;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Redirector
     */
    private $redirector;

    /**
     * SecurityContext constructor.
     *
     * @param SecurityContext $securityContext
     * @param LoggerInterface $logger
     * @param Redirector      $redirector
     */
    public function __construct(SecurityContext $securityContext, LoggerInterface $logger, Redirector $redirector)
    {
        $this->securityContext = $securityContext;
        $this->logger          = $logger;
        $this->redirector      = $redirector;
    }

    /**
     * Run the request filter.
     *
     * @param Request $request
     * @param \Closure $next
     * @param string $context
     *
     * @return mixed
     * @throws \Digbang\Security\Exceptions\Unauthorized
     */
    public function handle(Request $request, \Closure $next, $context)
    {
        list($context, $privacy) = $this->parseContext($context);

        $this->securityContext->bindContext($context, $request);

        try {
            $this->applySecurity($context, $privacy, $request);

            $response = $next($request);
        } catch (Unauthenticated $e) {
            $response = $this->redirector->guest(
                $this->getLoginUrl($context)
            );
        }

        $this->garbageCollect(
            $this->securityContext->getSecurity($context),
            $this->securityContext->getConfigurationFor($context)
        );

        return $response;
    }

    /**
     * Garbage collect activations and reminders.
     *
     * @param SecurityApi                  $security
     * @param SecurityContextConfiguration $configuration
     */
    protected function garbageCollect(SecurityApi $security, SecurityContextConfiguration $configuration)
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
                'Unable to garbage collect reminders or activations: '.
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
        return random_int(1, $lottery[1]) <= $lottery[0];
    }

    /**
     * @param string  $context
     * @param string  $privacy
     * @param Request $request
     *
     * @throws Unauthenticated
     * @throws Unauthorized
     */
    private function applySecurity($context, $privacy, Request $request)
    {
        if (mb_strtoupper($privacy) === 'PUBLIC') {
            return;
        }

        $security = $this->securityContext->getSecurity($context);

        if (! $security->getUser(true)) {
            throw Unauthenticated::guest($security)->inContext($context);
        }

        // Try to make the route, and let it explode upwards
        try {
            $route = $request->route();

            if ($route instanceof Route) {
                $route = $route->uri();
            }

            if ($route) {
                $security->url()->to($route);
            }
        } catch (Unauthorized $e) {
            throw $e->inContext($context);
        }
    }

    private function parseContext($context)
    {
        return array_pad(explode(':', $context, 2), 2, 'private');
    }

    private function getLoginUrl($context)
    {
        $security = $this->securityContext->getSecurity($context);

        return $security->getLoginUrl();
    }
}
