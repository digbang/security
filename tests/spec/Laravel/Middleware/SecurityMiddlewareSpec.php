<?php

namespace spec\Digbang\Security\Laravel\Middleware;

use Digbang\Security\Configurations\SecurityContextConfiguration;
use Digbang\Security\Contracts\SecurityApi;
use Digbang\Security\Exceptions\Unauthorized;
use Digbang\Security\SecurityContext;
use Digbang\Security\Users\User;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Routing\Route;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

/**
 * Class SecurityMiddlewareSpec.
 *
 * @mixin \Digbang\Security\Laravel\Middleware\SecurityMiddleware
 */
class SecurityMiddlewareSpec extends ObjectBehavior
{
    public function let(SecurityContext $securityContext, LoggerInterface $logger, SecurityApi $security, UrlGenerator $url, SecurityContextConfiguration $config, Redirector $redirector, RedirectResponse $redirect)
    {
        $securityContext->getSecurity('a_context')->willReturn($security);
        $securityContext->getConfigurationFor('a_context')->willReturn($config);
        $security->url()->willReturn($url);
        $security->getUser(Argument::any())->willReturn(null);
        $security->getLoginUrl()->willReturn('/auth/login');
        $redirector->guest(Argument::cetera())
            ->willReturn($redirect);

        $this->beConstructedWith($securityContext, $logger, $redirector);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Digbang\Security\Laravel\Middleware\SecurityMiddleware');
    }

    public function it_should_handle_a_request_for_a_given_context(SecurityContext $securityContext, Request $request, SecurityApi $security, User $user, UrlGenerator $url, Route $route)
    {
        $securityContext->bindContext('a_context', Argument::any())
            ->shouldBeCalled();

        $security->getUser(Argument::any())->willReturn($user);

        $request->route()->willReturn($route);
        $route->uri()->shouldBeCalled()->willReturn('/a/valid/url');
        $url->to('/a/valid/url')->willReturn('/a/valid/url');

        $next = function () {
            return 'Hello!';
        };

        $this->handle($request, $next, 'a_context')->shouldBe('Hello!');
    }

    public function it_should_return_a_redirect_response_when_user_is_not_logged_in(SecurityContext $securityContext, Request $request, SecurityApi $security)
    {
        $securityContext->bindContext('a_context', Argument::any())
            ->shouldBeCalled();
        $security->getLoginUrl()->shouldBeCalled();

        $next = function () {
            return 'Hello!';
        };

        $this->handle($request, $next, 'a_context')
            ->shouldBeAnInstanceOf(RedirectResponse::class);
    }

    public function it_should_throw_an_unauthorized_exception_when_user_does_not_have_permissions(SecurityContext $securityContext, Request $request, SecurityApi $security, User $user, UrlGenerator $url, Route $route)
    {
        $securityContext->bindContext('a_context', Argument::any())
            ->shouldBeCalled();

        $security->getUser(Argument::any())->willReturn($user);

        $request->route()->willReturn($route);
        $route->uri()->shouldBeCalled()->willReturn('/a/valid/url');
        $url->to('/a/valid/url')->willThrow(Unauthorized::class);

        $next = function () {
            return 'Hello!';
        };

        $this->shouldThrow(Unauthorized::class)
            ->duringHandle($request, $next, 'a_context');
    }

    public function it_should_not_check_for_authentication_on_public_routes(SecurityContext $securityContext, Request $request, SecurityApi $security)
    {
        $securityContext->bindContext('a_context', Argument::any())
            ->shouldBeCalled();

        $security->getUser()->shouldNotBeCalled();

        $next = function () {
            return 'Hello!';
        };

        $this->handle($request, $next, 'a_context:public');
    }
}
