<?php

namespace spec\Digbang\Security\Permissions;

use Digbang\Security\Permissions\Permission;
use Digbang\Security\Users\User;
use PhpSpec\ObjectBehavior;

class DefaultUserPermissionSpec extends ObjectBehavior
{
    public function let(User $user)
    {
        $this->beConstructedWith($user, 'testing_permission', true);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Digbang\Security\Permissions\DefaultUserPermission');
    }

    public function it_is_a_permission()
    {
        $this->shouldHaveType(Permission::class);
    }

    public function it_should_be_allowed()
    {
        $this->isAllowed()->shouldBe(true);
    }

    public function it_could_be_disallowed(User $user)
    {
        $this->beConstructedWith($user, 'testing_permission', false);

        $this->isAllowed()->shouldBe(false);
    }
}
