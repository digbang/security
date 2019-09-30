<?php

namespace spec\Digbang\Security\Permissions;

use Digbang\Security\Permissions\Permission;
use Digbang\Security\Roles\Role;
use PhpSpec\ObjectBehavior;

class DefaultRolePermissionSpec extends ObjectBehavior
{
    public function let(Role $role)
    {
        $this->beConstructedWith($role, 'testing_permission', true);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Digbang\Security\Permissions\DefaultRolePermission');
    }

    public function it_is_a_permission()
    {
        $this->shouldHaveType(Permission::class);
    }

    public function it_should_be_allowed()
    {
        $this->isAllowed()->shouldBe(true);
    }

    public function it_could_be_disallowed(Role $role)
    {
        $this->beConstructedWith($role, 'testing_permission', false);

        $this->isAllowed()->shouldBe(false);
    }
}
