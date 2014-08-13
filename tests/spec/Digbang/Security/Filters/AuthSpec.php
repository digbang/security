<?php namespace spec\Digbang\Security\Filters;

use Cartalyst\Sentry\Sentry;
use Digbang\Security\Permissions\Exceptions\PermissionException;
use Digbang\Security\Urls\SecureUrl;
use Illuminate\Routing\Redirector;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class AuthSpec
 * @mixin \Digbang\Security\Filters\Auth
 * @package spec\Digbang\Security\Filters
 */
class AuthSpec extends ObjectBehavior
{
    function it_is_initializable(Redirector $redirector, Sentry $sentry, SecureUrl $secureUrl)
    {
	    $this->beConstructedWith($redirector, $sentry, $secureUrl);
	    $this->shouldHaveType('Digbang\Security\Filters\Auth');
    }

	function it_should_filter_unlogged_users(Redirector $redirector, Sentry $sentry, SecureUrl $secureUrl)
	{
		$sentry->check()->willReturn(false);
		$secureUrl->route(Argument::cetera())->willReturn('some/url');
		$redirector->guest('some/url')->willReturn('aRedirectObject');

		$this->beConstructedWith($redirector, $sentry, $secureUrl);

		$this->logged()->shouldReturn('aRedirectObject');
	}

	function it_should_let_logged_users_pass(Redirector $redirector, Sentry $sentry, SecureUrl $secureUrl)
	{
		$sentry->check()->willReturn(true);

		$this->beConstructedWith($redirector, $sentry, $secureUrl);

		$this->logged()->shouldReturn(null);
	}

	function it_should_squeak_when_user_doesnt_have_permissions(Redirector $redirector, Sentry $sentry, SecureUrl $secureUrl)
	{
		$sentry->check()->willReturn(true);

		$secureUrl->may(Argument::cetera())->willThrow(new PermissionException());

		$this->beConstructedWith($redirector, $sentry, $secureUrl);

		$this->shouldThrow('Digbang\Security\Permissions\Exceptions\PermissionException')
			->duringWithPermissions('some.crazy.route');
	}

	function it_should_let_user_with_permissions_pass(Redirector $redirector, Sentry $sentry, SecureUrl $secureUrl)
	{
		$sentry->check()->willReturn(true);

		$secureUrl->may(Argument::cetera())->willReturn('a/valid/url');

		$this->beConstructedWith($redirector, $sentry, $secureUrl);

		$this->withPermissions('some.crazy.route')->shouldReturn(null);
	}
}
