<?php namespace spec\Digbang\Security\Permissions;

use Cartalyst\Sentinel\Permissions\PermissionsInterface;
use Digbang\Security\Permissions\DefaultPermission;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class LazyStandardPermissionsSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Digbang\Security\Permissions\LazyStandardPermissions');
    }

    function it_is_a_permissions_implementation()
    {
        $this->shouldHaveType(PermissionsInterface::class);
    }

	function it_allows_a_given_permission()
	{
		$permissions = [
			new DefaultPermission('foo.bar'),
			new DefaultPermission('bar.baz')
		];

		$this->beConstructedWith(new ArrayCollection($permissions));

		$this->hasAccess('foo.bar')->shouldBe(true);
		$this->hasAccess('bar.baz')->shouldBe(true);
		$this->hasAccess('bar.fooBar')->shouldBe(false);
	}

	function it_denies_a_given_permission()
	{
		$permissions = [
			new DefaultPermission('foo.bar'),
			new DefaultPermission('bar.baz', false)
		];

		$this->beConstructedWith(new ArrayCollection($permissions));

		$this->hasAccess('bar.baz')->shouldBe(false);
	}

	function it_allows_role_permissions()
	{
		$rolePermissions = [
			new ArrayCollection([
				new DefaultPermission('admin.permission'),
				new DefaultPermission('admin.destroy_system', false)
			])
		];

		$this->beConstructedWith(null, $rolePermissions);

		$this->hasAccess('admin.permission')->shouldBe(true);
		$this->hasAccess('admin.destroy_system')->shouldBe(false);
	}

	function it_overrides_role_permissions()
	{
		$permissions = [
			new DefaultPermission('admin.permission', false),
			new DefaultPermission('admin.destroy_system')
		];

		$rolePermissions = [
			new ArrayCollection([
				new DefaultPermission('admin.permission'),
				new DefaultPermission('admin.destroy_system', false),
				new DefaultPermission('admin.dont_override')
			])
		];

		$this->beConstructedWith(new ArrayCollection($permissions), $rolePermissions);

		$this->hasAccess('admin.permission')->shouldBe(false);
		$this->hasAccess('admin.destroy_system')->shouldBe(true);
		$this->hasAccess('admin.dont_override')->shouldBe(true);
	}

	function it_accepts_wildcard_permissions()
	{
		$permissions = [
			new DefaultPermission('admin.*'),
			new DefaultPermission('root.destroy_admin')
		];

		$this->beConstructedWith(new ArrayCollection($permissions));

		$this->hasAccess('admin.destroy_system')->shouldBe(true);
		$this->hasAccess('root.*')->shouldBe(true);
		$this->hasAccess('*.destroy_admin')->shouldBe(true);
		$this->hasAccess('*.destroy_system')->shouldBe(false);
		$this->hasAccess('*')->shouldBe(true);
	}

	function it_means_that_wildcard_permissions_can_be_dangerous()
	{
		$this->beConstructedWith(new ArrayCollection([new DefaultPermission('*')]));

		$this->hasAccess(uniqid())->shouldBe(true);
	}
}
