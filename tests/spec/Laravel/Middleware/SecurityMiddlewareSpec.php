<?php namespace spec\Digbang\Security\Laravel\Middleware;

use Digbang\Security\Configurations\SecurityContextConfiguration;
use Digbang\Security\Contracts\SecurityApi;
use Digbang\Security\Exceptions\Unauthenticated;
use Digbang\Security\Exceptions\Unauthorized;
use Digbang\Security\SecurityContext;
use Digbang\Security\Users\User;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

/**
 * Class SecurityMiddlewareSpec
 *
 * @package spec\Digbang\Security\Laravel\Middleware
 * @mixin \Digbang\Security\Laravel\Middleware\SecurityMiddleware
 */
class SecurityMiddlewareSpec extends ObjectBehavior
{
    function let(SecurityContext $securityContext, LoggerInterface $logger, Request $request, SecurityApi $security, Route $route, UrlGenerator $url, SecurityContextConfiguration $config)
    {
	    $securityContext->getSecurity('a_context')->willReturn($security);
	    $securityContext->getConfigurationFor('a_context')->willReturn($config);
	    $request->route()->willReturn($route);
	    $security->url()->willReturn($url);
	    $security->getUser(Argument::any())->willReturn(null);

        $this->beConstructedWith($securityContext, $logger);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Digbang\Security\Laravel\Middleware\SecurityMiddleware');
    }

	function it_should_handle_a_request_for_a_given_context(SecurityContext $securityContext, Request $request, SecurityApi $security, User $user, UrlGenerator $url)
	{
		$securityContext->bindContext('a_context', Argument::any())
			->shouldBeCalled();

		$security->getUser(Argument::any())->willReturn($user);

		$url->action(Argument::cetera())->willReturn('/a/valid/url');

		$next = function(){
			return 'Hello!';
		};

		$this->handle($request, $next, 'a_context')->shouldBe('Hello!');
	}

	function it_should_throw_an_unauthenticated_exception_when_user_is_not_logged_in(SecurityContext $securityContext, Request $request)
	{
		$securityContext->bindContext('a_context', Argument::any())
			->shouldBeCalled();

		$next = function(){
			return 'Hello!';
		};

		$this->shouldThrow(Unauthenticated::class)
			->duringHandle($request, $next, 'a_context');
	}

	function it_should_throw_an_unauthorized_exception_when_user_does_not_have_permissions(SecurityContext $securityContext, Request $request, SecurityApi $security, User $user, UrlGenerator $url)
	{
		$securityContext->bindContext('a_context', Argument::any())
			->shouldBeCalled();

		$security->getUser(Argument::any())->willReturn($user);

		$url->action(Argument::cetera())->willThrow(Unauthorized::class);

		$next = function(){
			return 'Hello!';
		};

		$this->shouldThrow(Unauthorized::class)
			->duringHandle($request, $next, 'a_context');
	}

	function it_should_not_check_for_authentication_on_public_routes(SecurityContext $securityContext, Request $request, SecurityApi $security)
	{
		$securityContext->bindContext('a_context', Argument::any())
			->shouldBeCalled();

		$security->getUser()->shouldNotBeCalled();

		$next = function(){
			return 'Hello!';
		};

		$this->handle($request, $next, 'a_context:public');
	}
}
