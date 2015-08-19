<?php namespace spec\Digbang\Security\Factories;

use Digbang\Security\Activations\ActivationRepository;
use Digbang\Security\Factories\RepositoryFactory;
use Digbang\Security\Permissions\PermissionRepository;
use Digbang\Security\Persistences\PersistenceRepository;
use Digbang\Security\Reminders\ReminderRepository;
use Digbang\Security\Roles\RoleRepository;
use Digbang\Security\Throttling\ThrottleRepository;
use Digbang\Security\Users\UserRepository;
use Illuminate\Contracts\Container\Container;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class ContainerBindingRepositoryFactorySpec
 *
 * @package spec\Digbang\Security\Factories
 * @mixin \Digbang\Security\Factories\ContainerBindingRepositoryFactory
 */
class ContainerBindingRepositoryFactorySpec extends ObjectBehavior
{
	function let(Container $container, RepositoryFactory $repositories, ActivationRepository $activationRepository, PermissionRepository $permissionRepository, PersistenceRepository $persistenceRepository, UserRepository $userRepository, RoleRepository $roleRepository, ReminderRepository $reminderRepository, ThrottleRepository $throttleRepository)
	{
		$repositories->createActivationRepository(Argument::any())->willReturn($activationRepository);
		$repositories->createPermissionRepository(Argument::cetera())->willReturn($permissionRepository);
		$repositories->createPersistenceRepository(Argument::cetera())->willReturn($persistenceRepository);
		$repositories->createUserRepository(Argument::cetera())->willReturn($userRepository);
		$repositories->createRoleRepository(Argument::cetera())->willReturn($roleRepository);
		$repositories->createReminderRepository(Argument::cetera())->willReturn($reminderRepository);
		$repositories->createThrottleRepository(Argument::cetera())->willReturn($throttleRepository);

		$this->beConstructedWith($container, $repositories);
	}

	function it_is_initializable()
	{
		$this->shouldHaveType('Digbang\Security\Factories\ContainerBindingRepositoryFactory');
	}

	function it_is_a_repository_factory()
	{
		$this->shouldHaveType(RepositoryFactory::class);
	}

	function it_should_make_user_repositories(PersistenceRepository $persistences, Container $container)
	{
		$container->instance(UserRepository::class, Argument::type(UserRepository::class))->shouldBeCalled();

		$this->createUserRepository('a_context', $persistences)->shouldBeAnInstanceOf(UserRepository::class);
	}

	function it_should_make_role_repositories(Container $container)
	{
		$container->instance(RoleRepository::class, Argument::type(RoleRepository::class))->shouldBeCalled();

		$this->createRoleRepository('a_context')->shouldBeAnInstanceOf(RoleRepository::class);
	}

	function it_should_make_permission_repositories(Container $container)
	{
		$container->instance(PermissionRepository::class, Argument::type(PermissionRepository::class))->shouldBeCalled();

		$this->createPermissionRepository('a_context')->shouldBeAnInstanceOf(PermissionRepository::class);
	}

	function it_should_make_persistence_repositories(Container $container)
	{
		$container->instance(PersistenceRepository::class, Argument::type(PersistenceRepository::class))->shouldBeCalled();

		$this->createPersistenceRepository('a_context')->shouldBeAnInstanceOf(PersistenceRepository::class);
	}

	function it_should_make_activation_repositories(Container $container)
	{
		$container->instance(ActivationRepository::class, Argument::type(ActivationRepository::class))->shouldBeCalled();

		$this->createActivationRepository('a_context')->shouldBeAnInstanceOf(ActivationRepository::class);
	}

	function it_should_make_reminder_repositories(UserRepository $users, Container $container)
	{
		$container->instance(ReminderRepository::class, Argument::type(ReminderRepository::class))->shouldBeCalled();

		$this->createReminderRepository('a_context', $users)->shouldBeAnInstanceOf(ReminderRepository::class);
	}

	function it_should_make_throttle_repositories(Container $container)
	{
		$container->instance(ThrottleRepository::class, Argument::type(ThrottleRepository::class))->shouldBeCalled();

		$this->createThrottleRepository('a_context')->shouldBeAnInstanceOf(ThrottleRepository::class);
	}
}
