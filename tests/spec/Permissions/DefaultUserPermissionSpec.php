<?php namespace spec\Digbang\Security\Permissions;

use Digbang\Security\Permissions\Permission;
use Digbang\Security\Users\User;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DefaultUserPermissionSpec extends ObjectBehavior
{
    function let(User $user)
    {
        $this->beConstructedWith($user, 'testing_permission', true);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Digbang\Security\Permissions\DefaultUserPermission');
    }

    function it_is_a_permission()
    {
        $this->shouldHaveType(Permission::class);
    }

    function it_should_be_allowed()
    {
        $this->isAllowed()->shouldBe(true);
    }

    function it_could_be_disallowed(User $user)
    {
        $this->beConstructedWith($user, 'testing_permission', false);

        $this->isAllowed()->shouldBe(false);
    }
}
