<?php namespace spec\Digbang\Security\Urls;

use Cartalyst\Sentry\Users\UserInterface;
use Digbang\Security\Contracts\PermissionRepositoryInterface;
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
	protected $validRoute      = 'a.valid.route';
	protected $validAction     = 'A\Valid\Controller@action';
	protected $validPath       = '/a/valid/path';
	protected $validPermission = 'a.valid.permission';
	protected $url             = '/a/valid/url';

	function let(UserInterface $user, UrlGenerator $url, PermissionRepositoryInterface $permissionRepo)
	{
		$user->hasPermission(Argument::exact($this->validPermission))->willReturn(true);
		$user->hasPermission(Argument::not(  $this->validPermission))->willReturn(false);

		$url->route(  Argument::cetera() )->willReturn($this->url);
		$url->action( Argument::cetera() )->willReturn($this->url);
		$url->to(     Argument::cetera() )->willReturn($this->url);


		$permissionRepo->getForRoute( $this->validRoute )->willReturn($this->validPermission);
		$permissionRepo->getForAction($this->validAction)->willReturn($this->validPermission);
		$permissionRepo->getForPath(  $this->validPath  )->willReturn($this->validPermission);

		$permissionRepo->getForRoute( Argument::not($this->validRoute ))->willReturn('an.invalid.permission');
		$permissionRepo->getForAction(Argument::not($this->validAction))->willReturn('an.invalid.permission');
		$permissionRepo->getForPath(  Argument::not($this->validPath  ))->willReturn('an.invalid.permission');

		$this->beConstructedWith($user, $url, $permissionRepo);
	}

	function it_is_initializable()
	{
	    $this->shouldHaveType('Digbang\Security\Urls\SecureUrl');
	}

	function it_should_make_urls_by_route_if_the_user_has_a_permission()
	{
		$this->route($this->validRoute)->shouldReturn($this->url);
	}

	function it_should_squeak_when_trying_to_build_a_route_url_and_user_doesnt_have_permissions()
	{
		$this->shouldThrow('Digbang\Security\Exceptions\PermissionException')
			->duringRoute('any.invalid.route');
	}

	function it_should_make_urls_by_action_if_the_user_has_a_permission()
	{
		$this->action($this->validAction)->shouldReturn($this->url);
	}

	function it_should_squeak_when_trying_to_build_an_action_url_and_user_doesnt_have_permissions()
	{
		$this->shouldThrow('Digbang\Security\Exceptions\PermissionException')
			->duringAction('Any\Invalid\Controller@action');
	}

	function it_should_validate_urls_if_the_user_has_a_permission()
	{
		$this->may($this->validPath)->shouldReturn($this->url);
	}

	function it_should_squeak_when_trying_to_validate_a_url_and_user_doesnt_have_permissions()
	{
		$this->shouldThrow('Digbang\Security\Exceptions\PermissionException')
			->duringMay('/any/invalid/url');
	}
}
