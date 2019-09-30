<?php

namespace spec\Digbang\Security\Factories;

use Digbang\Security\Activations\ActivationRepository;
use Digbang\Security\Configurations\SecurityContextConfiguration;
use Digbang\Security\Factories\RepositoryFactory;
use Digbang\Security\Permissions\PermissionRepository;
use Digbang\Security\Persistences\PersistenceRepository;
use Digbang\Security\Reminders\ReminderRepository;
use Digbang\Security\Roles\RoleRepository;
use Digbang\Security\SecurityContext;
use Digbang\Security\Throttling\ThrottleRepository;
use Digbang\Security\Users\UserRepository;
use Illuminate\Contracts\Container\Container;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class ConfigurationRepositoryFactorySpec.
 *
 * @mixin \Digbang\Security\Factories\ConfigurationRepositoryFactory
 */
class ConfigurationRepositoryFactorySpec extends ObjectBehavior
{
    public function let(Container $container, SecurityContext $securityContext, RepositoryFactory $defaults, SecurityContextConfiguration $config, ActivationRepository $activationRepository, PermissionRepository $permissionRepository, PersistenceRepository $persistenceRepository, UserRepository $userRepository, RoleRepository $roleRepository, ReminderRepository $reminderRepository, ThrottleRepository $throttleRepository)
    {
        $container->make(SecurityContext::class)->willReturn($securityContext);
        $securityContext->getConfigurationFor(Argument::any())
            ->willReturn($config);

        $defaults->createActivationRepository(Argument::any())->willReturn($activationRepository);
        $defaults->createPermissionRepository(Argument::cetera())->willReturn($permissionRepository);
        $defaults->createPersistenceRepository(Argument::cetera())->willReturn($persistenceRepository);
        $defaults->createUserRepository(Argument::cetera())->willReturn($userRepository);
        $defaults->createRoleRepository(Argument::cetera())->willReturn($roleRepository);
        $defaults->createReminderRepository(Argument::cetera())->willReturn($reminderRepository);
        $defaults->createThrottleRepository(Argument::cetera())->willReturn($throttleRepository);

        $this->beConstructedWith($container, $defaults);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Digbang\Security\Factories\ConfigurationRepositoryFactory');
    }

    public function it_is_a_repository_factory()
    {
        $this->shouldHaveType(RepositoryFactory::class);
    }

    public function it_should_make_user_repositories(PersistenceRepository $persistences, RoleRepository $roles)
    {
        $this->createUserRepository('a_context', $persistences, $roles)
            ->shouldBeAnInstanceOf(UserRepository::class);
    }

    public function it_should_make_role_repositories()
    {
        $this->createRoleRepository('a_context')->shouldBeAnInstanceOf(RoleRepository::class);
    }

    public function it_shouldnt_make_role_repositories_when_they_are_disabled(SecurityContextConfiguration $config, RepositoryFactory $defaults)
    {
        $config->isRolesEnabled()->shouldBeCalled()->willReturn(false);
        $defaults->createRoleRepository(Argument::any())->shouldNotBeCalled();

        $this->createRoleRepository('a_context')->shouldBeAnInstanceOf(RoleRepository::class);
    }

    public function it_should_make_permission_repositories()
    {
        $this->createPermissionRepository('a_context')->shouldBeAnInstanceOf(PermissionRepository::class);
    }

    public function it_shouldnt_make_permission_repositories_when_they_are_disabled(SecurityContextConfiguration $config, RepositoryFactory $defaults)
    {
        $config->isPermissionsEnabled()->shouldBeCalled()->willReturn(false);
        $defaults->createPermissionRepository(Argument::any())->shouldNotBeCalled();

        $this->createPermissionRepository('a_context')->shouldBeAnInstanceOf(PermissionRepository::class);
    }

    public function it_should_make_persistence_repositories(SecurityContextConfiguration $config, PersistenceRepository $persistenceRepository)
    {
        $config->isSinglePersistence()->shouldBeCalled()->willReturn(false);
        $config->getPersistenceRepository()->shouldBeCalled()->willReturn(null);
        $persistenceRepository->setPersistenceMode('multi')->shouldBeCalled();

        $this->createPersistenceRepository('a_context')->shouldBeAnInstanceOf(PersistenceRepository::class);
    }

    public function it_should_make_activation_repositories(SecurityContextConfiguration $config, ActivationRepository $activationRepository)
    {
        $config->getActivationRepository()->shouldBeCalled()->willReturn(null);
        $config->getActivationsExpiration()->shouldBeCalled()->willReturn(1234);
        $activationRepository->setExpires(1234)->shouldBeCalled();

        $this->createActivationRepository('a_context')->shouldBeAnInstanceOf(ActivationRepository::class);
    }

    public function it_should_make_reminder_repositories(UserRepository $users, SecurityContextConfiguration $config, ReminderRepository $reminderRepository)
    {
        $config->getReminderRepository()->shouldBeCalled()->willReturn(null);
        $config->getRemindersExpiration()->shouldBeCalled()->willReturn(1234);
        $reminderRepository->setExpires(1234)->shouldBeCalled();

        $this->createReminderRepository('a_context', $users)->shouldBeAnInstanceOf(ReminderRepository::class);
    }

    public function it_should_make_throttle_repositories(SecurityContextConfiguration $config, ThrottleRepository $throttleRepository)
    {
        $config->getThrottleRepository()->shouldBeCalled()->willReturn(null);

        $config->getGlobalThrottleInterval()->shouldBeCalled()->willReturn(1234);
        $config->getGlobalThrottleThresholds()->shouldBeCalled()->willReturn(4321);
        $config->getIpThrottleInterval()->shouldBeCalled()->willReturn(123);
        $config->getIpThrottleThresholds()->shouldBeCalled()->willReturn(321);
        $config->getUserThrottleInterval()->shouldBeCalled()->willReturn(12);
        $config->getUserThrottleThresholds()->shouldBeCalled()->willReturn(21);

        $throttleRepository->setGlobalInterval(1234)->shouldBeCalled();
        $throttleRepository->setGlobalThresholds(4321)->shouldBeCalled();
        $throttleRepository->setIpInterval(123)->shouldBeCalled();
        $throttleRepository->setIpThresholds(321)->shouldBeCalled();
        $throttleRepository->setUserInterval(12)->shouldBeCalled();
        $throttleRepository->setUserThresholds(21)->shouldBeCalled();

        $this->createThrottleRepository('a_context')->shouldBeAnInstanceOf(ThrottleRepository::class);
    }
}
