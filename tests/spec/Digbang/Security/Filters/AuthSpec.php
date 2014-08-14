<?php namespace spec\Digbang\Security\Filters;

use Digbang\Security\Auth\AccessControl;
use Digbang\Security\Permissions\Exceptions\PermissionException;
use Digbang\Security\Urls\SecureUrl;
use Illuminate\Routing\Redirector;
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
    function it_is_initializable(Redirector $redirector, AccessControl $accessControl, SecureUrl $secureUrl)
    {
	    $this->beConstructedWith($redirector, $accessControl, $secureUrl);
	    $this->shouldHaveType('Digbang\Security\Filters\Auth');
    }

	function it_should_filter_unlogged_users(Redirector $redirector, AccessControl $accessControl, SecureUrl $secureUrl)
	{
		$accessControl->isLogged()->willReturn(false);
		$secureUrl->route(Argument::cetera())->willReturn('some/url');
		$redirector->guest('some/url')->willReturn('aRedirectObject');

		$this->beConstructedWith($redirector, $accessControl, $secureUrl);

		$this->logged()->shouldReturn('aRedirectObject');
	}

	function it_should_let_logged_users_pass(Redirector $redirector, AccessControl $accessControl, SecureUrl $secureUrl)
	{
		$accessControl->isLogged()->willReturn(true);

		$this->beConstructedWith($redirector, $accessControl, $secureUrl);

		$this->logged()->shouldReturn(null);
	}

	function it_should_squeak_when_user_doesnt_have_permissions(Redirector $redirector, AccessControl $accessControl, SecureUrl $secureUrl, Route $route)
	{
		$accessControl->isLogged()->willReturn(true);

		$secureUrl->action(Argument::cetera())->willThrow(new PermissionException());

		$this->beConstructedWith($redirector, $accessControl, $secureUrl);

		$this->shouldThrow('Digbang\Security\Permissions\Exceptions\PermissionException')
			->duringWithPermissions($route);
	}

	function it_should_let_user_with_permissions_pass(Redirector $redirector, AccessControl $accessControl, SecureUrl $secureUrl, Route $route)
	{
		$accessControl->isLogged()->willReturn(true);

		$secureUrl->action(Argument::cetera())->willReturn('a/valid/url');

		$this->beConstructedWith($redirector, $accessControl, $secureUrl);

		$this->withPermissions($route)->shouldReturn(null);
	}
}
