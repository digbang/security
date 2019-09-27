<?php

namespace spec\Digbang\Security\Permissions;

use Cartalyst\Sentinel\Permissions\PermissionsInterface;
use Digbang\Security\Permissions\DefaultRolePermission;
use Digbang\Security\Permissions\DefaultUserPermission;
use Digbang\Security\Roles\Role;
use Digbang\Security\Users\User;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;

class LazyStandardPermissionsSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType('Digbang\Security\Permissions\LazyStandardPermissions');
    }

    public function it_is_a_permissions_implementation()
    {
        $this->shouldHaveType(PermissionsInterface::class);
    }

    public function it_allows_a_given_permission(User $user)
    {
        $user = $user->getWrappedObject();

        $permissions = [
            new DefaultUserPermission($user, 'foo.bar'),
            new DefaultUserPermission($user, 'bar.baz'),
        ];

        $this->beConstructedWith(new ArrayCollection($permissions));

        $this->hasAccess('foo.bar')->shouldBe(true);
        $this->hasAccess('bar.baz')->shouldBe(true);
        $this->hasAccess('bar.fooBar')->shouldBe(false);
    }

    public function it_denies_a_given_permission(User $user)
    {
        $user = $user->getWrappedObject();

        $permissions = [
            new DefaultUserPermission($user, 'foo.bar'),
            new DefaultUserPermission($user, 'bar.baz', false),
        ];

        $this->beConstructedWith(new ArrayCollection($permissions));

        $this->hasAccess('bar.baz')->shouldBe(false);
    }

    public function it_allows_role_permissions(Role $role)
    {
        $role = $role->getWrappedObject();

        $rolePermissions = [
            new ArrayCollection([
                new DefaultRolePermission($role, 'admin.permission'),
                new DefaultRolePermission($role, 'admin.destroy_system', false),
            ]),
        ];

        $this->beConstructedWith(null, $rolePermissions);

        $this->hasAccess('admin.permission')->shouldBe(true);
        $this->hasAccess('admin.destroy_system')->shouldBe(false);
    }

    public function it_overrides_role_permissions(User $user, Role $role)
    {
        $user = $user->getWrappedObject();
        $role = $role->getWrappedObject();
        $permissions = [
            new DefaultUserPermission($user, 'admin.permission', false),
            new DefaultUserPermission($user, 'admin.destroy_system'),
        ];

        $rolePermissions = [
            new ArrayCollection([
                new DefaultRolePermission($role, 'admin.permission'),
                new DefaultRolePermission($role, 'admin.destroy_system', false),
                new DefaultRolePermission($role, 'admin.dont_override'),
            ]),
        ];

        $this->beConstructedWith(new ArrayCollection($permissions), $rolePermissions);

        $this->hasAccess('admin.permission')->shouldBe(false);
        $this->hasAccess('admin.destroy_system')->shouldBe(true);
        $this->hasAccess('admin.dont_override')->shouldBe(true);
    }

    public function it_accepts_wildcard_permissions(User $user)
    {
        $user = $user->getWrappedObject();

        $permissions = [
            new DefaultUserPermission($user, 'admin.*'),
            new DefaultUserPermission($user, 'root.destroy_admin'),
        ];

        $this->beConstructedWith(new ArrayCollection($permissions));

        $this->hasAccess('admin.destroy_system')->shouldBe(true);
        $this->hasAccess('root.*')->shouldBe(true);
        $this->hasAccess('*.destroy_admin')->shouldBe(true);
        $this->hasAccess('*.destroy_system')->shouldBe(false);
        $this->hasAccess('*')->shouldBe(true);
    }

    public function it_means_that_wildcard_permissions_can_be_dangerous(User $user)
    {
        $user = $user->getWrappedObject();

        $this->beConstructedWith(new ArrayCollection([new DefaultUserPermission($user, '*')]));

        $this->hasAccess(uniqid())->shouldBe(true);
    }
}
