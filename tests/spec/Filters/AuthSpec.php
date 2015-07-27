<?php namespace spec\Digbang\Security\Filters;

use Digbang\Security\Auth\AccessControl;
use Digbang\Security\Permissions\Exceptions\PermissionException;
use Digbang\Security\Urls\SecureUrl;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Routing\Route;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class AuthSpec
 * @mixin \PhpSpec\Wrapper\Subject
 * @mixin \Digbang\Security\Filters\Auth
 * @package spec\Digbang\Security\Filters
 */
class AuthSpec extends ObjectBehavior
{
	function let(ResponseFactory $responseFactory, AccessControl $accessControl, SecureUrl $secureUrl, Repository $config)
	{
		$this->beConstructedWith($responseFactory, $accessControl, $secureUrl, $config);
	}

    function it_is_initializable()
    {
	    $this->shouldHaveType('Digbang\Security\Filters\Auth');
    }

	function it_should_filter_unlogged_users(ResponseFactory $responseFactory, AccessControl $accessControl, SecureUrl $secureUrl, UrlGenerator $urlGenerator, Repository $config)
	{
		$accessControl->isLogged()->shouldBeCalled()->willReturn(false);
		$secureUrl->insecure()->shouldBeCalled()->willReturn($urlGenerator);
		$config->get('digbang.security.auth.login_route')->shouldBeCalled()->willReturn('a.named.login.route');

		$urlGenerator->route('a.named.login.route')->shouldBeCalled()->willReturn('some/url');
		$responseFactory->redirectGuest('some/url')->shouldBeCalled()->willReturn('aRedirectObject');

		$this->logged()->shouldReturn('aRedirectObject');
	}

	function it_should_let_logged_users_pass(AccessControl $accessControl)
	{
		$accessControl->isLogged()->willReturn(true);

		$this->logged()->shouldReturn(null);
	}

	function it_should_squeak_when_user_doesnt_have_permissions(AccessControl $accessControl, SecureUrl $secureUrl, Route $route)
	{
		$accessControl->isLogged()->willReturn(true);

		$secureUrl->action(Argument::cetera())->willThrow(new PermissionException());

		$this->shouldThrow(PermissionException::class)
			->duringWithPermissions($route);
	}

	function it_should_let_user_with_permissions_pass(AccessControl $accessControl, SecureUrl $secureUrl, Route $route)
	{
		$accessControl->isLogged()->willReturn(true);

		$secureUrl->action(Argument::cetera())->willReturn('a/valid/url');

		$this->withPermissions($route)->shouldReturn(null);
	}
}
