<?php namespace spec\Digbang\Security\Urls;

use Cartalyst\Sentry\Users\UserInterface;
use Illuminate\Routing\UrlGenerator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class SecureUrlSpec
 * @mixin \Digbang\Security\Urls\SecureUrl
 * @package spec\Digbang\Security\Urls
 */
class SecureUrlSpec extends ObjectBehavior
{
	protected $validPermission = 'a.valid.permission';
	protected $url = '/a/valid/url';

	function let(UserInterface $user, UrlGenerator $url)
	{
		$user->hasPermission(Argument::exact($this->validPermission))->willReturn(true);
		$user->hasPermission(Argument::not($this->validPermission))->willReturn(false);

		$url->route(Argument::any(), Argument::any())->willReturn($this->url);
		$url->action(Argument::any(), Argument::any())->willReturn($this->url);
		$url->to(Argument::any(), Argument::any(), Argument::any())->willReturn($this->url);

		$this->beConstructedWith($user, $url);
	}

    function it_is_initializable()
    {
        $this->shouldHaveType('Digbang\Security\Urls\SecureUrl');
    }

	function it_should_make_urls_by_route_if_the_user_has_a_permission()
	{
		$this->route('a.specific.route', $this->validPermission)->shouldReturn($this->url);
	}

	function it_should_squeak_when_trying_to_build_a_route_url_and_user_doesnt_have_permissions()
	{
		$this->shouldThrow('Digbang\Security\Exceptions\PermissionException')
			->duringRoute('a.specific.route', 'whatever.invalid.permission');
	}

	function it_should_make_urls_by_action_if_the_user_has_a_permission()
	{
		$this->action('A\Specific\Controller@action', $this->validPermission)->shouldReturn($this->url);
	}

	function it_should_squeak_when_trying_to_build_an_action_url_and_user_doesnt_have_permissions()
	{
		$this->shouldThrow('Digbang\Security\Exceptions\PermissionException')
			->duringAction('A\Specific\Controller@action', 'whatever.invalid.permission');
	}

	function it_should_validate_urls_if_the_user_has_a_permission()
	{
		$this->may($this->url, $this->validPermission)->shouldReturn($this->url);
	}

	function it_should_squeak_when_trying_to_validate_a_url_and_user_doesnt_have_permissions()
	{
		$this->shouldThrow('Digbang\Security\Exceptions\PermissionException')
			->duringMay($this->url, 'whatever.invalid.permission');
	}
}
