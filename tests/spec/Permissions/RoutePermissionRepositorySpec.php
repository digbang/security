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
	function let(Router $router)
	{
		$this->beConstructedWith($router);
	}

    function it_is_initializable()
    {
        $this->shouldHaveType('Digbang\Security\Permissions\RoutePermissionRepository');
    }

	function it_should_return_the_same_route_for_route_permissions(Router $router, Route $routeA, Route $routeB, Route $routeC)
	{
		$aValidRoute = 'the.valid.route';

		$routeA->getName()->shouldBeCalled()->willReturn('the.invalid.route');
		$routeB->getName()->shouldBeCalled()->willReturn('an.invalid.route');
		$routeC->getName()->shouldBeCalled()->willReturn($aValidRoute);
		$routeC->getAction()->shouldBeCalled()->willReturn(['permission' => $aValidRoute]);

		$router->getRoutes()->shouldBeCalled()->willReturn([$routeA, $routeB, $routeC]);

		$this->getForRoute($aValidRoute)->shouldReturn($aValidRoute);
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
		$routeC->getName()->willReturn($aValidRoute);

		$routeC->getAction()->shouldBeCalled()->willReturn(['permission' => $aValidRoute]);

		$this->getForAction($aValidAction)->shouldReturn($aValidRoute);
	}

	function it_should_return_all_permissions(Router $router, Route $routeA, Route $routeB, Route $routeC)
	{
		$routeA->getAction()->shouldBeCalled()->willReturn(['permission' => 'a.given.route']);
		$routeB->getAction()->shouldBeCalled()->willReturn(['permission' => 'another.permission']);
		$routeC->getAction()->shouldBeCalled()->willReturn([]);
		$router->getRoutes()->shouldBeCalled()->willReturn([$routeA, $routeB, $routeC]);

		$this->all()->shouldReturn(['a.given.route', 'another.permission']);
	}

	function it_should_extract_permissions_from_routes(Route $route)
	{
		$aValidRoute = 'the.valid.route';

		$route->getAction()->shouldBeCalled()->willReturn(['permission' => $aValidRoute]);

		$this->extractPermissionFrom($route)->shouldReturn($aValidRoute);
	}
}
