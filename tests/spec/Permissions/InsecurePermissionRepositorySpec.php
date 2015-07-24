<?php namespace spec\Digbang\Security\Permissions;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class InsecurePermissionRepositorySpec
 * @package spec\Digbang\Security\Permissions
 * @mixin \Digbang\Security\Permissions\InsecurePermissionRepository
 */
class InsecurePermissionRepositorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Digbang\Security\Permissions\InsecurePermissionRepository');
        $this->shouldHaveType('Digbang\Security\Permissions\PermissionRepository');
    }

	function it_should_return_empty_permissions_for_every_request()
	{
		$this->getForRoute(  uniqid() )->shouldReturn(null);
		$this->getForAction( uniqid() )->shouldReturn(null);
		$this->getForPath(   uniqid() )->shouldReturn(null);
	}

	function it_should_return_an_empty_array_when_asked_for_all_permissions()
	{
		$this->all()->shouldReturn([]);
	}
}
