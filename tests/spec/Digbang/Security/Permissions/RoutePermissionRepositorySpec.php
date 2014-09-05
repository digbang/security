<?php namespace spec\Digbang\Security\Permissions;

use Illuminate\Config\Repository;
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
    function it_is_initializable(Router $router, Repository $config)
    {
	    $config->get('security::permissions.prefix')->willReturn('backoffice');
	    $this->beConstructedWith($router, $config);
        $this->shouldHaveType('Digbang\Security\Permissions\RoutePermissionRepository');
    }

	function it_should_return_the_same_route_for_route_permissions(Router $router, Repository $config, Route $routeA, Route $routeB, Route $routeC)
	{
		$config->get('security::permissions.prefix')->willReturn('the');
		$aValidRoute = 'the.valid.route';

		$routeA->getName()->shouldBeCalled()->willReturn('the.invalid.route');
		$routeB->getName()->shouldBeCalled()->willReturn('an.invalid.route');
		$routeC->getName()->shouldBeCalled()->willReturn($aValidRoute);

		$routeC->getAction()->shouldBeCalled()->willReturn(['permission' => $aValidRoute]);

		$router->getRoutes()->shouldBeCalled()->willReturn([$routeA, $routeB, $routeC]);

		$this->beConstructedWith($router, $config);

		$this->getForRoute($aValidRoute)->shouldReturn($aValidRoute);
	}

	function it_should_extract_permissions_from_routes(Router $router, Repository $config, Route $route)
	{
		$aValidRoute = 'the.valid.route';

		$route->getAction()->shouldBeCalled()->willReturn(['permission' => $aValidRoute]);
		$this->beConstructedWith($router, $config);

		$this->extractPermissionFrom($route)->shouldReturn($aValidRoute);
	}

	function it_should_return_all_permissions(Router $router, Repository $config, Route $routeA, Route $routeB, Route $routeC)
	{
		$config->get('security::permissions.prefix')->willReturn('');

		$routeA->getAction()->shouldBeCalled()->willReturn(['permission' => 'a.given.route']);
		$routeB->getAction()->shouldBeCalled()->willReturn(['permission' => 'another.permission']);
		$routeC->getAction()->shouldBeCalled()->willReturn([]);

		$router->getRoutes()->shouldBeCalled()->willReturn([$routeA, $routeB, $routeC]);

		$this->beConstructedWith($router, $config);
		$this->all()->shouldReturn(['a.given.route', 'another.permission']);
	}

	function it_should_return_some_permissions_filtered_by_prefix(Router $router, Repository $config, Route $routeA, Route $routeB, Route $routeC)
	{
		$config->get('security::permissions.prefix')->willReturn('a');

		$routeA->getName()->shouldBeCalled()->willReturn('a.given.route');
		$routeB->getName()->shouldBeCalled()->willReturn('other.given.route');
		$routeC->getName()->shouldBeCalled()->willReturn(null);

		$routeA->getAction()->shouldBeCalled()->willReturn(['permission' => 'a.given.route']);
		$routeB->getAction()->shouldNotBeCalled();
		$routeC->getAction()->shouldNotBeCalled();

		$router->getRoutes()->shouldBeCalled()->willReturn([$routeA, $routeB, $routeC]);

		$this->beConstructedWith($router, $config);
		$this->all()->shouldReturn(['a.given.route']);
	}

	function it_should_return_a_route_given_a_valid_action(Router $router, Repository $config, Route $routeA, Route $routeB, Route $routeC)
	{
		$config->get('security::permissions.prefix')->willReturn('');
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

		$this->beConstructedWith($router, $config);
		$this->getForAction($aValidAction)->shouldReturn($aValidRoute);
	}

	function it_should_return_a_route_given_a_valid_action_and_prefix(Router $router, Repository $config, Route $routeA, Route $routeB, Route $routeC)
	{
		$config->get('security::permissions.prefix')->willReturn('the');
		$aValidAction = 'A\\Valid\\Action@name';
		$aValidRoute = 'the.valid.route';

		$router->getRoutes()->shouldBeCalled()->willReturn([$routeA, $routeB, $routeC]);

		$routeA->getActionName()->shouldBeCalled()->willReturn('Some\\Action@name');
		$routeB->getActionName()->shouldBeCalled()->willReturn('Some\\OtherAction@name');
		$routeC->getActionName()->shouldBeCalled()->willReturn($aValidAction);

		$routeA->getName()->shouldBeCalled()->willReturn('the.some.route');
		$routeB->getName()->shouldBeCalled()->willReturn('the.other.route');
		$routeC->getName()->shouldBeCalled()->willReturn($aValidRoute);

		$routeC->getAction()->shouldBeCalled()->willReturn(['permission' => $aValidRoute]);

		$this->beConstructedWith($router, $config);
		$this->getForAction($aValidAction)->shouldReturn($aValidRoute);
	}
}
