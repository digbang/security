<?php

namespace spec\Digbang\Security\Factories;

use Digbang\Security\Activations\ActivationRepository;
use Digbang\Security\Factories\RepositoryFactory;
use Digbang\Security\Permissions\PermissionRepository;
use Digbang\Security\Persistences\PersistenceRepository;
use Digbang\Security\Reminders\ReminderRepository;
use Digbang\Security\Roles\RoleRepository;
use Digbang\Security\Throttling\ThrottleRepository;
use Digbang\Security\Users\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Illuminate\Contracts\Container\Container;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Prophecy\Prophet;

/**
 * Class DefaultRepositoryFactorySpec.
 *
 * @mixin \Digbang\Security\Factories\DefaultRepositoryFactory
 */
class DefaultRepositoryFactorySpec extends ObjectBehavior
{
    public function let(Container $container, EntityManager $entityManager, ClassMetadata $metadata)
    {
        $container->make(EntityManager::class, Argument::any())
            ->willReturn($entityManager);

        $entityManager->getClassMetadata(Argument::any())->willReturn($metadata);

        $container->make(Argument::type('string'), Argument::cetera())
            ->will(function ($args) {
                return (new Prophet)->prophesize($args[0]);
            });

        $this->beConstructedWith($container);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Digbang\Security\Factories\DefaultRepositoryFactory');
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
        $this->createRoleRepository('a_context')
            ->shouldBeAnInstanceOf(RoleRepository::class);
    }

    public function it_should_make_permission_repositories()
    {
        $this->createPermissionRepository('a_context')
            ->shouldBeAnInstanceOf(PermissionRepository::class);
    }

    public function it_should_make_persistence_repositories()
    {
        $this->createPersistenceRepository('a_context')
            ->shouldBeAnInstanceOf(PersistenceRepository::class);
    }

    public function it_should_make_activation_repositories()
    {
        $this->createActivationRepository('a_context')
            ->shouldBeAnInstanceOf(ActivationRepository::class);
    }

    public function it_should_make_reminder_repositories(UserRepository $users)
    {
        $this->createReminderRepository('a_context', $users)
            ->shouldBeAnInstanceOf(ReminderRepository::class);
    }

    public function it_should_make_throttle_repositories()
    {
        $this->createThrottleRepository('a_context')
            ->shouldBeAnInstanceOf(ThrottleRepository::class);
    }
}
