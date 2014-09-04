<?php namespace spec\Digbang\Security\Permissions;

use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class RoutePermissionRepositorySpec
 * @package spec\Digbang\Security\Permissions
 * @mixin \Digbang\Security\Permissions\RoutePermissionRepository
 */
class RoutePermissionRepositorySpec extends ObjectBehavior
{
    function it_is_initializable(Router $router)
    {
	    $this->beConstructedWith($router);
        $this->shouldHaveType('Digbang\Security\Permissions\RoutePermissionRepository');
    }

	function it_should_return_the_same_route_for_route_permissions(Router $router)
	{
		$this->beConstructedWith($router);
		$this->getForRoute('a.given.route')->shouldReturn('a.given.route');
	}

	function it_should_return_all_permissions(Router $router, Route $routeA, Route $routeB, Route $routeC)
	{
		$router->getRoutes()->shouldBeCalled()->willReturn([$routeA, $routeB, $routeC]);
		$routeA->getName()->shouldBeCalled()->willReturn('a.given.route');
		$routeB->getName()->shouldBeCalled()->willReturn('other.given.route');
		$routeC->getName()->shouldBeCalled()->willReturn(null);

		$this->beConstructedWith($router);
		$this->all()->shouldReturn(['a.given.route', 'other.given.route']);
	}

	function it_should_return_a_route_given_a_valid_action(Router $router, Route $routeA, Route $routeB, Route $routeC)
	{
		$aValidAction = 'A\\Valid\\Action@name';
		$aValidRoute = 'the.valid.route';

		$router->getRoutes()->shouldBeCalled()->willReturn([$routeA, $routeB, $routeC]);

		$routeA->getActionName()->shouldBeCalled()->willReturn('Some\\Action@name');
		$routeB->getActionName()->shouldBeCalled()->willReturn('Some\\OtherAction@name');
		$routeC->getActionName()->shouldBeCalled()->willReturn($aValidAction);

		$routeA->getName()->shouldNotBeCalled();
		$routeB->getName()->shouldNotBeCalled();
		$routeC->getName()->shouldBeCalled()->willReturn($aValidRoute);

		$this->beConstructedWith($router);
		$this->getForAction($aValidAction)->shouldReturn($aValidRoute);
	}
}
