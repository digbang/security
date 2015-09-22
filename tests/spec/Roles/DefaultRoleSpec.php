<?php namespace spec\Digbang\Security\Roles;

use Digbang\Security\Roles\Role;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class DefaultRoleSpec
 *
 * @package spec\Digbang\Security\Roles
 * @mixin \Digbang\Security\Roles\DefaultRole
 */
class DefaultRoleSpec extends ObjectBehavior
{
    function let()
    {
	    $this->beConstructedWith('A Very Clever Role Name');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Digbang\Security\Roles\DefaultRole');
    }

	function it_is_a_role()
	{
		$this->shouldHaveType(Role::class);
	}

	function it_should_compare_by_id(Role $role)
	{
		$role->getRoleId()->willReturn(123);
		$this->is($role)->shouldBe(false);
	}

	function it_should_compare_by_slug()
	{
		$this->is('admin')->shouldBe(false);
		$this->is('a-very-clever-role-name')->shouldBe(true);
	}

	function it_should_mutate_names()
	{
		$this->getName()->shouldBe('A Very Clever Role Name');
		$this->setName('Dull name');
		$this->getName()->shouldBe('Dull name');
	}

	function it_should_mutate_slugs()
	{
		$this->getRoleSlug()->shouldBe('a-very-clever-role-name');
		$this->setRoleSlug('dull-slug');
		$this->getRoleSlug()->shouldBe('dull-slug');
	}
}
