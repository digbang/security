<?php namespace spec\Digbang\Security\Users;

use Carbon\Carbon;
use Cartalyst\Sentinel\Permissions\PermissionsInterface;
use Digbang\Security\Permissions\LazyStandardPermissions;
use Digbang\Security\Permissions\Permissible;
use Digbang\Security\Roles\Role;
use Digbang\Security\Users\ValueObjects\Email;
use Digbang\Security\Users\ValueObjects\Password;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Prophecy\Prophet;

/**
 * Class DefaultUserSpec
 *
 * @package spec\Digbang\Security\Users
 * @mixin \Digbang\Security\Users\DefaultUser
 */
class DefaultUserSpec extends ObjectBehavior
{
	function let(Email $email, Password $password)
	{
		$this->beConstructedWith(
			$email, $password, 'testing_username'
		);
	}

    function it_is_initializable()
    {
        $this->shouldHaveType('Digbang\Security\Users\DefaultUser');
    }

    function it_is_a_default_implementation_of_user()
    {
        $this->shouldHaveType('Digbang\Security\Users\User');
    }

	function it_is_roleable()
	{
		$this->shouldHaveType('Digbang\Security\Roles\Roleable');
	}

	function it_is_permissible()
	{
		$this->shouldHaveType('Digbang\Security\Permissions\Permissible');
	}

	function it_may_be_updated()
	{
		$this->getName()->getFirstName()->shouldBe('');
		$this->getName()->getLastName()->shouldBe('');

		$this->update([
			'firstName' => 'John',
			'lastName'  => 'Doe'
		]);

		$this->getName()->getFirstName()->shouldBe('John');
		$this->getName()->getLastName()->shouldBe('Doe');
	}

	function it_should_reveal_its_email_address(Email $email)
	{
		$email->getAddress()->shouldBeCalled()->willReturn('john.doe@example.com');
		$this->getUserLogin()->shouldBe('john.doe@example.com');
	}

	function it_should_reveal_its_password_hash(Password $password)
	{
		$password->getHash()->shouldBeCalled()->willReturn('rubbish');
		$this->getUserPassword()->shouldBe('rubbish');
	}

	function it_should_reveal_its_username()
	{
		$this->getUsername()->shouldBe('testing_username');
	}

	function it_should_accumulate_roles()
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

	function it_should_generate_random_codes_for_persistences()
	{
		$this->generatePersistenceCode()->shouldMatch('/^\w{32}$/');
	}

	function it_should_touch_the_last_login_date()
	{
		Carbon::setTestNow($now = Carbon::now());

		$this->recordLogin();
		$this->getLastLogin()->shouldBeLike($now);
	}

	function it_should_hold_a_null_permissions_instance_just_in_case()
	{
		$this->hasAccess('foo')->shouldBe(false);
		$this->hasAnyAccess('foo')->shouldBe(false);
	}

	function it_should_accept_permissions_factory_methods(PermissionsInterface $permissions)
	{
		$this->setPermissionsFactory(function() use ($permissions){
			return $permissions->getWrappedObject();
		});

		$this->getPermissionsInstance()->shouldBe($permissions);
	}

	function it_should_use_the_given_factory_to_process_permissions(PermissionsInterface $permissions)
	{
		$permissions->hasAccess('foo')->shouldBeCalled()->willReturn(true);
		$permissions->hasAccess('bar')->shouldBeCalled()->willReturn(false);

		$permissions->hasAccess(['foo', 'bar'])->shouldBeCalled()->willReturn(false);
		$permissions->hasAnyAccess(['foo', 'bar'])->shouldBeCalled()->willReturn(true);

		$this->setPermissionsFactory(function() use ($permissions){
			return $permissions->getWrappedObject();
		});

		$this->hasAccess('foo')->shouldBe(true);
		$this->hasAccess('bar')->shouldBe(false);

		$this->hasAccess(['foo', 'bar'])->shouldBe(false);
		$this->hasAnyAccess(['foo', 'bar'])->shouldBe(true);
	}

	function it_should_hold_permissions()
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

	function it_should_delegate_to_the_password_object_to_check_itself(Password $password)
	{
		$password->check('foo')->shouldBeCalled()->willReturn(false);
		$password->check('bar')->shouldBeCalled()->willReturn(true);

		$this->checkPassword('foo')->shouldBe(false);
		$this->checkPassword('bar')->shouldBe(true);
	}
}
