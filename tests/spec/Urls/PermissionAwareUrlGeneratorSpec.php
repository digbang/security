<?php namespace spec\Digbang\Security\Urls;

use Digbang\Security\Contracts\SecurityApi;
use Digbang\Security\Permissions\Permissible;
use Digbang\Security\Permissions\PermissionException;
use Digbang\Security\Permissions\PermissionRepository;
use Digbang\Security\Users\User;
use Illuminate\Routing\UrlGenerator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Prophecy\Prophet;

/**
 * Class SecureUrlSpec
 * @mixin \Digbang\Security\Urls\PermissionAwareUrlGenerator
 * @package spec\Digbang\Security\Urls
 */
class PermissionAwareUrlGeneratorSpec extends ObjectBehavior
{
	const SECURE_ROUTE     = 'a.valid.route';
	const SECURE_ACTION    = 'A\Valid\Controller@action';
	const SECURE_PATH      = '/a/valid/path';
	const INSECURE_ROUTE   = 'an.insecure.route';
	const INSECURE_ACTION  = 'An\Insecure\Controller@action';
	const INSECURE_PATH    = '/an/insecure/path';
	const VALID_PERMISSION = 'a.valid.permission';
	const URL              = '/a/valid/url';

	function let(UrlGenerator $url, PermissionRepository $permissionRepo, SecurityApi $securityApi)
	{
		$url->route( Argument::cetera())->willReturn(self::URL);
		$url->action(Argument::cetera())->willReturn(self::URL);
		$url->to(self::SECURE_PATH, Argument::cetera())->willReturn(self::URL);
		$url->to(self::INSECURE_PATH, Argument::cetera())->willReturn(self::INSECURE_PATH);
		$url->to(Argument::not(self::SECURE_PATH), Argument::cetera())->willReturn('/an/invalid/url');

		$permissionRepo->getForRoute( self::SECURE_ROUTE )->willReturn(self::VALID_PERMISSION);
		$permissionRepo->getForAction(self::SECURE_ACTION)->willReturn(self::VALID_PERMISSION);
		$permissionRepo->getForPath(  self::URL         )->willReturn(self::VALID_PERMISSION);

		$permissionRepo->getForRoute( self::INSECURE_ROUTE )->willReturn(null);
		$permissionRepo->getForAction(self::INSECURE_ACTION)->willReturn(null);
		$permissionRepo->getForPath(  self::INSECURE_PATH  )->willReturn(null);

		$permissionRepo->getForRoute( Argument::not(self::SECURE_ROUTE ))->willReturn('an.invalid.permission');
		$permissionRepo->getForAction(Argument::not(self::SECURE_ACTION))->willReturn('an.invalid.permission');
		$permissionRepo->getForPath(  Argument::not(self::SECURE_PATH  ))->willReturn('an.invalid.permission');

		$this->beConstructedWith($url, $permissionRepo, $securityApi);
	}

	function withUser(SecurityApi $securityApi)
	{
		$user = (new Prophet)->prophesize(User::class);
		$user->willImplement(Permissible::class);

		$user->hasAccess(Argument::exact(self::VALID_PERMISSION))->willReturn(true);
		$user->hasAccess(Argument::not(  self::VALID_PERMISSION))->willReturn(false);

		$securityApi->getUser()->willReturn($user);
	}

	function it_is_initializable_without_user()
	{
		$this->shouldHaveType('Digbang\Security\Urls\PermissionAwareUrlGenerator');
		$this->shouldHaveType('Digbang\Security\Urls\PermissibleUrlGenerator');
	}

	function it_is_initializable_with_user(SecurityApi $securityApi)
	{
		$this->withUser($securityApi);

	    $this->shouldHaveType('Digbang\Security\Urls\PermissionAwareUrlGenerator');
	    $this->shouldHaveType('Digbang\Security\Urls\PermissibleUrlGenerator');
	}

	function it_should_make_urls_by_route_if_the_user_has_a_permission(SecurityApi $securityApi)
	{
		$this->withUser($securityApi);

		$this->route(self::SECURE_ROUTE)->shouldReturn(self::URL);
	}

	function it_should_squeak_when_trying_to_build_a_route_url_and_user_doesnt_have_permissions(SecurityApi $securityApi)
	{
		$this->withUser($securityApi);

		$this->shouldThrow(PermissionException::class)->duringRoute('any.invalid.route');
	}

	function it_should_make_urls_by_action_if_the_user_has_a_permission(SecurityApi $securityApi)
	{
		$this->withUser($securityApi);

		$this->action(self::SECURE_ACTION)->shouldReturn(self::URL);
	}

	function it_should_squeak_when_trying_to_build_an_action_url_and_user_doesnt_have_permissions(SecurityApi $securityApi)
	{
		$this->withUser($securityApi);

		$this->shouldThrow(PermissionException::class)->duringAction('Any\Invalid\Controller@action');
	}

	function it_should_make_urls_by_path_if_the_user_has_a_permission(SecurityApi $securityApi)
	{
		$this->withUser($securityApi);

		$this->to(self::SECURE_PATH)->shouldReturn(self::URL);
	}

	function it_should_squeak_when_trying_to_build_a_path_url_and_user_doesnt_have_permissions(SecurityApi $securityApi)
	{
		$this->withUser($securityApi);

		$this->shouldThrow(PermissionException::class)->duringTo('/any/invalid/path');
	}

	function it_should_make_insecure_urls_by_route_without_user()
	{
		$this->route(self::INSECURE_ROUTE)->shouldReturn(self::URL);
	}

	function it_should_make_insecure_urls_by_action_without_user()
	{
		$this->action(self::INSECURE_ACTION)->shouldReturn(self::URL);
	}

	function it_should_make_insecure_urls_by_path_without_user()
	{
		$this->to(self::INSECURE_PATH)->shouldReturn(self::INSECURE_PATH);
	}

	function it_should_squeak_when_requested_secure_urls_by_route_without_user()
	{
		$this->shouldThrow(PermissionException::class)->duringRoute(self::SECURE_ROUTE);
	}

	function it_should_squeak_when_requested_secure_urls_by_action_without_user()
	{
		$this->shouldThrow(PermissionException::class)->duringAction(self::SECURE_ACTION);
	}

	function it_should_squeak_when_requested_secure_urls_by_path_without_user()
	{
		$this->shouldThrow(PermissionException::class)->duringTo(self::SECURE_PATH);
	}

	function it_should_give_me_the_best_allowed_route_based_on_the_current_users_permissions(SecurityApi $securityApi)
	{
		$this->withUser($securityApi);

		$this->bestRoute([self::SECURE_ROUTE])->shouldReturn(self::URL);
		$this->bestRoute([self::INSECURE_ROUTE])->shouldReturn(self::URL);
	}

	function it_should_give_me_null_with_no_user_permissions()
	{
		$this->bestRoute([self::SECURE_ROUTE])->shouldReturn(null);
	}

	function it_should_give_me_the_best_allowed_action_based_on_the_current_users_permissions(SecurityApi $securityApi)
	{
		$this->withUser($securityApi);

		$this->bestAction([self::SECURE_ACTION])->shouldReturn(self::URL);
		$this->bestAction([self::INSECURE_ACTION])->shouldReturn(self::URL);
	}

	function it_should_give_me_null_on_an_action_with_no_user_permissions()
	{
		$this->bestAction([self::SECURE_ACTION])->shouldReturn(null);
	}
}
