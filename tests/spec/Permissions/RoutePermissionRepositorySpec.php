<?php namespace spec\Digbang\Security\Permissions;

use Digbang\Security\Permissions\PermissionRepository;
use Digbang\Security\Permissions\RoutePermissionRepository;
use Illuminate\Routing\Route;
use Illuminate\Routing\RouteCollection;
use Illuminate\Routing\Router;
use Illuminate\Support\Collection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class RoutePermissionRepositorySpec
 * @package spec\Digbang\Security\Permissions
 * @mixin RoutePermissionRepository
 */
class RoutePermissionRepositorySpec extends ObjectBehavior
{
	const VALID_NAME       = 'the.valid.route';
	const VALID_ACTION     = 'A\\Valid\\Action@name';
	const VALID_PERMISSION = 'a.valid.permission';

	function let(Router $router, Route $route, RouteCollection $routeCollection)
	{
		$router->getRoutes()->willReturn($routeCollection);
		$route->getAction()->willReturn(['permission' => self::VALID_PERMISSION]);

		$routeCollection->getByName(self::VALID_NAME)->willReturn($route);
		$routeCollection->getByAction(self::VALID_ACTION)->willReturn($route);

		$routeCollection->getByName(Argument::not(self::VALID_NAME))->willReturn(null);
		$routeCollection->getByAction(Argument::not(self::VALID_ACTION))->willReturn(null);

		$this->beConstructedWith($router);
	}

    function it_is_initializable()
    {
        $this->shouldHaveType(RoutePermissionRepository::class);
    }

    function it_is_a_permissions_repository_instance()
    {
        $this->shouldHaveType(PermissionRepository::class);
    }

	function it_should_return_a_valid_permission_for_a_valid_route(Router $router, RouteCollection $routeCollection)
	{
		$router->getRoutes()->shouldBeCalled();
		$routeCollection->getByName(self::VALID_NAME)->shouldBeCalled();

		$this->getForRoute(self::VALID_NAME)->shouldReturn(self::VALID_PERMISSION);
	}

	function it_should_return_a_valid_permission_for_a_valid_action(Router $router, RouteCollection $routeCollection)
	{
		$router->getRoutes()->shouldBeCalled();
		$routeCollection->getByAction(self::VALID_ACTION)->shouldBeCalled();

		$this->getForAction(self::VALID_ACTION)->shouldReturn(self::VALID_PERMISSION);
	}

	function it_should_return_null_for_an_invalid_route(Router $router, RouteCollection $routeCollection)
	{
		$router->getRoutes()->shouldBeCalled();
		$routeCollection->getByName('invalid.route.name')->shouldBeCalled();

		$this->getForRoute('invalid.route.name')->shouldReturn(null);
	}

	function it_should_return_null_for_an_invalid_action(Router $router, RouteCollection $routeCollection)
	{
		$router->getRoutes()->shouldBeCalled();
		$routeCollection->getByAction('An\\Invalid@action')->shouldBeCalled();

		$this->getForAction('An\\Invalid@action')->shouldReturn(null);
	}

	function it_should_return_all_permissions(Router $router, RouteCollection $routeCollection, Route $route)
	{
		$router->getRoutes()->shouldBeCalled();
		$routeCollection->getIterator()->shouldBeCalled()
			->willReturn(new Collection([$route->getWrappedObject()]));

		$this->all()->shouldReturn([self::VALID_PERMISSION]);
	}
}
