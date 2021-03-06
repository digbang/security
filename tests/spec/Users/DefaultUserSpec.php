<?php

namespace spec\Digbang\Security\Users;

use Carbon\Carbon;
use Cartalyst\Sentinel\Permissions\PermissionsInterface;
use Digbang\Security\Activations\Activation;
use Digbang\Security\Permissions\DefaultRolePermission;
use Digbang\Security\Permissions\LazyStandardPermissions;
use Digbang\Security\Permissions\Permissible;
use Digbang\Security\Permissions\Permission;
use Digbang\Security\Roles\DefaultRole;
use Digbang\Security\Roles\Role;
use Digbang\Security\Users\ValueObjects\Email;
use Digbang\Security\Users\ValueObjects\Password;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Prophet;

/**
 * Class DefaultUserSpec.
 *
 * @mixin \Digbang\Security\Users\DefaultUser
 */
class DefaultUserSpec extends ObjectBehavior
{
    public function let(string $email, string $password)
    {
        $this->beConstructedWith(
            $email, $password, 'testing_username'
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Digbang\Security\Users\DefaultUser');
    }

    public function it_is_a_default_implementation_of_user()
    {
        $this->shouldHaveType('Digbang\Security\Users\User');
    }

    public function it_is_roleable()
    {
        $this->shouldHaveType('Digbang\Security\Roles\Roleable');
    }

    public function it_is_permissible()
    {
        $this->shouldHaveType('Digbang\Security\Permissions\Permissible');
    }

    public function it_may_be_updated()
    {
        $this->getName()->getFirstName()->shouldBe('');
        $this->getName()->getLastName()->shouldBe('');

        $this->update([
            'firstName' => 'John',
            'lastName' => 'Doe',
        ]);

        $this->getName()->getFirstName()->shouldBe('John');
        $this->getName()->getLastName()->shouldBe('Doe');
    }

    public function it_should_reveal_its_email_address(Email $email)
    {
        $email->getAddress()->shouldBeCalled()->willReturn('john.doe@example.com');
        $this->getUserLogin()->shouldBe('john.doe@example.com');
    }

    public function it_should_reveal_its_password_hash(Password $password)
    {
        $password->getHash()->shouldBeCalled()->willReturn('rubbish');
        $this->getUserPassword()->shouldBe('rubbish');
    }

    public function it_should_reveal_its_username()
    {
        $this->getUsername()->shouldBe('testing_username');
    }

    public function it_should_accumulate_roles()
    {
        $role = (new Prophet)->prophesize(Role::class);
        $role->willImplement(Permissible::class);
        $role->is($role)->willReturn(true);
        $role->getPermissions()->willReturn([]);

        $this->inRole($role)->shouldBe(false);

        $this->addRole($role);

        $this->inRole($role)->shouldBe(true);

        $this->getRoles()->getValues()->shouldBe([$role]);
    }

    public function it_should_generate_random_codes_for_persistences()
    {
        $this->generatePersistenceCode()->shouldMatch('/^\w{32}$/');
    }

    public function it_should_touch_the_last_login_date()
    {
        Carbon::setTestNow($now = Carbon::now());

        $this->recordLogin();
        $this->getLastLogin()->shouldBeLike($now);
    }

    public function it_should_hold_a_null_permissions_instance_just_in_case()
    {
        $this->hasAccess('foo')->shouldBe(false);
        $this->hasAnyAccess('foo')->shouldBe(false);
    }

    public function it_should_accept_permissions_factory_methods(PermissionsInterface $permissions)
    {
        $this->setPermissionsFactory(function () use ($permissions) {
            return $permissions->getWrappedObject();
        });

        $this->getPermissionsInstance()->shouldBe($permissions);
    }

    public function it_should_use_the_given_factory_to_process_permissions(PermissionsInterface $permissions)
    {
        $permissions->hasAccess('foo')->shouldBeCalled()->willReturn(true);
        $permissions->hasAccess('bar')->shouldBeCalled()->willReturn(false);

        $permissions->hasAccess(['foo', 'bar'])->shouldBeCalled()->willReturn(false);
        $permissions->hasAnyAccess(['foo', 'bar'])->shouldBeCalled()->willReturn(true);

        $this->setPermissionsFactory(function () use ($permissions) {
            return $permissions->getWrappedObject();
        });

        $this->hasAccess('foo')->shouldBe(true);
        $this->hasAccess('bar')->shouldBe(false);

        $this->hasAccess(['foo', 'bar'])->shouldBe(false);
        $this->hasAnyAccess(['foo', 'bar'])->shouldBe(true);
    }

    public function it_should_hold_permissions()
    {
        // Will collaborate with the real one
        $this->setPermissionsFactory(LazyStandardPermissions::getFactory());

        $this->hasAccess('foo')->shouldBe(false);
        $this->addPermission('foo', true);
        $this->hasAccess('foo')->shouldBe(true);
        $this->updatePermission('foo', false);
        $this->hasAccess('foo')->shouldBe(false);

        $this->updatePermission('bar', true, false);
        $this->hasAccess('bar')->shouldBe(false);

        $this->hasAccess('baz')->shouldBe(false);
        $this->updatePermission('baz', true, true);
        $this->hasAccess('baz')->shouldBe(true);
        $this->removePermission('baz');
        $this->hasAccess('baz')->shouldBe(false);
    }

    public function it_should_allow_a_permission()
    {
        $this->setPermissionsFactory(LazyStandardPermissions::getFactory());

        $this->hasAccess('foo')->shouldBe(false);
        $this->allow('foo');
        $this->hasAccess('foo')->shouldBe(true);
        $this->hasAnyAccess(['fooes', 'bar', 'baz'])->shouldBe(false);
        $this->allow(['fooes', 'bar', 'baz']);
        $this->hasAccess('foo')->shouldBe(true);
        $this->hasAccess('bar')->shouldBe(true);
        $this->hasAccess('baz')->shouldBe(true);
    }

    public function it_should_deny_a_permission(DefaultRole $role, DefaultRolePermission $permission)
    {
        $role->getPermissions()->willReturn(new ArrayCollection([$permission->getWrappedObject()]));
        $permission->getName()->willReturn('foo');
        $permission->isAllowed()->willReturn(true);

        $this->addRole($role);

        $this->setPermissionsFactory(LazyStandardPermissions::getFactory());

        $this->hasAccess('foo')->shouldBe(true);

        $this->deny('foo');
        $this->hasAccess('foo')->shouldBe(false);
    }

    public function it_should_delegate_to_the_password_object_to_check_itself(Password $password)
    {
        $password->check('foo')->shouldBeCalled()->willReturn(false);
        $password->check('bar')->shouldBeCalled()->willReturn(true);

        $this->checkPassword('foo')->shouldBe(false);
        $this->checkPassword('bar')->shouldBe(true);
    }

    public function it_should_know_if_its_not_activated()
    {
        $this->isActivated()->shouldBe(false);
    }

    public function it_should_know_if_its_activated(Activation $activation)
    {
        $activation->isCompleted()->willReturn(true);

        $activations = $this->getActivations();
        $activations->add($activation);

        $this->isActivated()->shouldBe(true);
    }

    public function it_should_know_when_it_was_activated(Activation $activation)
    {
        $activation->isCompleted()->willReturn(true);
        $activation->getCompletedAt()->willReturn($now = Carbon::now());

        $activations = $this->getActivations();
        $activations->add($activation);

        $this->getActivatedAt()->shouldBe($now);
    }

    public function it_should_sync_permissions()
    {
        $role = new DefaultRole('Testing role');
        $role->setPermissionsFactory(LazyStandardPermissions::getFactory());
        $this->setPermissionsFactory(LazyStandardPermissions::getFactory());

        $role->allow(['role.foo', 'role.bar']);
        $role->deny('role.baz');

        $this->addRole($role);

        $this->hasAccess('role.foo')->shouldBe(true);
        $this->hasAccess('role.bar')->shouldBe(true);
        $this->hasAccess('role.baz')->shouldBe(false);

        $this->syncPermissions(['foo', 'role.foo', 'role.baz']);

        $this->hasAccess('foo')->shouldBe(true);
        $this->hasAccess('role.foo')->shouldBe(true);
        $this->hasAccess('role.bar')->shouldBe(false);
        $this->hasAccess('role.baz')->shouldBe(true);
    }

    public function it_shouldnt_hold_allowed_user_permissions_that_the_role_already_allows()
    {
        $role = new DefaultRole('Testing role');
        $role->setPermissionsFactory(LazyStandardPermissions::getFactory());
        $this->setPermissionsFactory(LazyStandardPermissions::getFactory());

        $role->allow(['role.foo', 'role.bar']);
        $role->deny('role.baz');
        $this->addRole($role);

        $this->deny('role.foo');

        $this->syncPermissions(['foo', 'role.foo', 'role.baz']);

        $permissions = $this->getPermissions();

        $permissions->filter(function (Permission $permission) {
            return $permission->getName() == 'role.foo' && $permission->isAllowed();
        })->isEmpty()->shouldBe(true);
    }

    public function it_should_clear_permissions_when_updated_with_an_empty_permissions_value()
    {
        $this->setPermissionsFactory(LazyStandardPermissions::getFactory());

        $this->allow(['role.foo', 'role.bar']);
        $this->hasAccess('role.foo')->shouldBe(true);

        $this->update(['permissions' => null]);
        $this->hasAccess('role.foo')->shouldBe(false);
    }

    public function it_should_ignore_updating_a_password_with_an_empty_value(Password $password)
    {
        $password->getHash()->willReturn('a-hash');

        $this->update(['password' => '']);

        $this->getUserPassword()->shouldBe('a-hash');
    }
}
