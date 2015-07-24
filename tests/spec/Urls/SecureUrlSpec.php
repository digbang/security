<?php namespace spec\Digbang\Security\Urls;

use Cartalyst\Sentry\Sentry;
use Digbang\Security\Permissions\PermissionRepository;
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
	/**
	 * @var \Prophecy\Prophet
	 */
	protected $prophet;

	protected $secureRoute     = 'a.valid.route';
	protected $secureAction    = 'A\Valid\Controller@action';
	protected $securePath      = '/a/valid/path';
	protected $insecureRoute   = 'an.insecure.route';
	protected $insecureAction  = 'An\Insecure\Controller@action';
	protected $insecurePath    = '/an/insecure/path';
	protected $validPermission = 'a.valid.permission';
	protected $url             = '/a/valid/url';

	function let()
	{
		$this->prophet = new \Prophecy\Prophet;
	}

	protected function withoutUser(UrlGenerator $url, PermissionRepository $permissionRepo)
	{
		$url->route(  Argument::cetera() )->willReturn($this->url);
		$url->action( Argument::cetera() )->willReturn($this->url);
		$url->to(     Argument::cetera() )->willReturn($this->url);

		$permissionRepo->getForRoute( $this->secureRoute )->willReturn($this->validPermission);
		$permissionRepo->getForAction($this->secureAction)->willReturn($this->validPermission);
		$permissionRepo->getForPath(  $this->securePath  )->willReturn($this->validPermission);

		$permissionRepo->getForRoute( $this->insecureRoute )->willReturn(null);
		$permissionRepo->getForAction($this->insecureAction)->willReturn(null);
		$permissionRepo->getForPath(  $this->insecurePath  )->willReturn(null);

		$permissionRepo->getForRoute( Argument::not($this->secureRoute ))->willReturn('an.invalid.permission');
		$permissionRepo->getForAction(Argument::not($this->secureAction))->willReturn('an.invalid.permission');
		$permissionRepo->getForPath(  Argument::not($this->securePath  ))->willReturn('an.invalid.permission');
	}

	protected function withUser(UrlGenerator $url, PermissionRepository $permissionRepo, Sentry $sentry)
	{
		$this->withoutUser($url, $permissionRepo);

		$user = $this->prophet->prophesize('Cartalyst\Sentry\Users\UserInterface');

		$user->hasAccess(Argument::exact($this->validPermission))->willReturn(true);
		$user->hasAccess(Argument::not(  $this->validPermission))->willReturn(false);

		$sentry->getUser()->willReturn($user);
	}

	protected function beConstructedWithUser(UrlGenerator $url, PermissionRepository $permissionRepo, Sentry $sentry)
	{
		$this->withUser($url, $permissionRepo, $sentry);

		$this->beConstructedWith($url, $permissionRepo, $sentry);
	}

	protected function beConstructedWithoutUser(UrlGenerator $url, PermissionRepository $permissionRepo, Sentry $sentry)
	{
		$this->withoutUser($url, $permissionRepo);

		$this->beConstructedWith($url, $permissionRepo, $sentry);
	}

	function it_is_initializable_with_user(UrlGenerator $url, PermissionRepository $permissionRepo, Sentry $sentry)
	{
		$this->beConstructedWithUser($url, $permissionRepo, $sentry);

	    $this->shouldHaveType('Digbang\Security\Urls\SecureUrl');
	}

	function it_is_initializable_without_user(UrlGenerator $url, PermissionRepository $permissionRepo, Sentry $sentry)
	{
		$this->beConstructedWithoutUser($url, $permissionRepo, $sentry);

	    $this->shouldHaveType('Digbang\Security\Urls\SecureUrl');
	}

	function it_should_make_urls_by_route_if_the_user_has_a_permission(UrlGenerator $url, PermissionRepository $permissionRepo, Sentry $sentry)
	{
		$this->beConstructedWithUser($url, $permissionRepo, $sentry);

		$this->route($this->secureRoute)->shouldReturn($this->url);
	}

	function it_should_squeak_when_trying_to_build_a_route_url_and_user_doesnt_have_permissions(UrlGenerator $url, PermissionRepository $permissionRepo, Sentry $sentry)
	{
		$this->beConstructedWithUser($url, $permissionRepo, $sentry);

		$this->shouldThrow('Digbang\Security\Permissions\Exceptions\PermissionException')
			->duringRoute('any.invalid.route');
	}

	function it_should_make_urls_by_action_if_the_user_has_a_permission(UrlGenerator $url, PermissionRepository $permissionRepo, Sentry $sentry)
	{
		$this->beConstructedWithUser($url, $permissionRepo, $sentry);

		$this->action($this->secureAction)->shouldReturn($this->url);
	}

	function it_should_squeak_when_trying_to_build_an_action_url_and_user_doesnt_have_permissions(UrlGenerator $url, PermissionRepository $permissionRepo, Sentry $sentry)
	{
		$this->beConstructedWithUser($url, $permissionRepo, $sentry);

		$this->shouldThrow('Digbang\Security\Permissions\Exceptions\PermissionException')
			->duringAction('Any\Invalid\Controller@action');
	}

	function it_should_validate_urls_if_the_user_has_a_permission(UrlGenerator $url, PermissionRepository $permissionRepo, Sentry $sentry)
	{
		$this->beConstructedWithUser($url, $permissionRepo, $sentry);

		$this->may($this->securePath)->shouldReturn($this->url);
	}

	function it_should_squeak_when_trying_to_validate_a_url_and_user_doesnt_have_permissions(UrlGenerator $url, PermissionRepository $permissionRepo, Sentry $sentry)
	{
		$this->beConstructedWithUser($url, $permissionRepo, $sentry);

		$this->shouldThrow('Digbang\Security\Permissions\Exceptions\PermissionException')
			->duringMay('/any/invalid/url');
	}

	function it_should_make_insecure_urls_by_route_without_user(UrlGenerator $url, PermissionRepository $permissionRepo, Sentry $sentry)
	{
		$this->beConstructedWithoutUser($url, $permissionRepo, $sentry);

		$this->route($this->insecureRoute)->shouldReturn($this->url);
	}

	function it_should_make_insecure_urls_by_action_without_user(UrlGenerator $url, PermissionRepository $permissionRepo, Sentry $sentry)
	{
		$this->beConstructedWithoutUser($url, $permissionRepo, $sentry);

		$this->action($this->insecureAction)->shouldReturn($this->url);
	}

	function it_should_make_insecure_urls_by_path_without_user(UrlGenerator $url, PermissionRepository $permissionRepo, Sentry $sentry)
	{
		$this->beConstructedWithoutUser($url, $permissionRepo, $sentry);

		$this->may($this->insecurePath)->shouldReturn($this->url);
	}

	function it_should_squeak_when_requested_secure_urls_by_route_without_user(UrlGenerator $url, PermissionRepository $permissionRepo, Sentry $sentry)
	{
		$this->beConstructedWithoutUser($url, $permissionRepo, $sentry);

		$this->shouldThrow('Digbang\Security\Permissions\Exceptions\PermissionException')
			->duringRoute($this->secureRoute);
	}

	function it_should_squeak_when_requested_secure_urls_by_action_without_user(UrlGenerator $url, PermissionRepository $permissionRepo, Sentry $sentry)
	{
		$this->beConstructedWithoutUser($url, $permissionRepo, $sentry);

		$this->shouldThrow('Digbang\Security\Permissions\Exceptions\PermissionException')
			->duringAction($this->secureAction);
	}

	function it_should_squeak_when_requested_secure_urls_by_path_without_user(UrlGenerator $url, PermissionRepository $permissionRepo, Sentry $sentry)
	{
		$this->beConstructedWithoutUser($url, $permissionRepo, $sentry);

		$this->shouldThrow('Digbang\Security\Permissions\Exceptions\PermissionException')
			->duringMay($this->securePath);
	}

	function it_should_give_me_the_best_allowed_route_based_on_the_current_users_permissions(UrlGenerator $url, PermissionRepository $permissionRepo, Sentry $sentry)
	{
		$this->beConstructedWithUser($url, $permissionRepo, $sentry);

		$this->bestRoute([$this->secureRoute])->shouldReturn($this->url);
		$this->bestRoute([$this->insecureRoute])->shouldReturn($this->url);
	}

	function it_should_give_me_null_with_no_user_permissions(UrlGenerator $url, PermissionRepository $permissionRepo, Sentry $sentry)
	{
		$this->beConstructedWithoutUser($url, $permissionRepo, $sentry);

		$this->bestRoute([$this->secureRoute])->shouldReturn(null);
	}

	function it_should_give_me_the_best_allowed_action_based_on_the_current_users_permissions(UrlGenerator $url, PermissionRepository $permissionRepo, Sentry $sentry)
	{
		$this->beConstructedWithUser($url, $permissionRepo, $sentry);

		$this->bestAction([$this->secureAction])->shouldReturn($this->url);
		$this->bestAction([$this->insecureAction])->shouldReturn($this->url);
	}

	function it_should_give_me_null_on_an_action_with_no_user_permissions(UrlGenerator $url, PermissionRepository $permissionRepo, Sentry $sentry)
	{
		$this->beConstructedWithoutUser($url, $permissionRepo, $sentry);

		$this->bestAction([$this->secureAction])->shouldReturn(null);
	}
}