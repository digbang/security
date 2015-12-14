<?php namespace Digbang\Security\Factories;

use Digbang\Security\Activations\ActivationRepository;
use Digbang\Security\Permissions\PermissionRepository;
use Digbang\Security\Persistences\PersistenceRepository;
use Digbang\Security\Reminders\ReminderRepository;
use Digbang\Security\Roles\RoleRepository;
use Digbang\Security\Throttling\ThrottleRepository;
use Digbang\Security\Users\UserRepository;
use Illuminate\Contracts\Container\Container;

class ContainerBindingRepositoryFactory implements RepositoryFactory
{
	/**
	 * @var Container
	 */
	private $container;

	/**
	 * @var RepositoryFactory
	 */
	private $repositories;

	/**
	 * ContainerBindingRepositoryFactory constructor.
	 *
	 * @param Container         $container
	 * @param RepositoryFactory $repositories
	 */
	public function __construct(Container $container, RepositoryFactory $repositories)
	{
		$this->container = $container;
		$this->repositories = $repositories;
	}

	/**
	 * {@inheritdoc}
	 */
	public function createPersistenceRepository($context)
	{
		return $this->bindAndReturn(
			PersistenceRepository::class,
			$this->repositories->createPersistenceRepository($context)
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function createUserRepository($context, PersistenceRepository $persistenceRepository, RoleRepository $roleRepository)
	{
		return $this->bindAndReturn(
			UserRepository::class,
			$this->repositories->createUserRepository($context, $persistenceRepository, $roleRepository)
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function createRoleRepository($context)
	{
		return $this->bindAndReturn(
			RoleRepository::class,
			$this->repositories->createRoleRepository($context)
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function createActivationRepository($context)
	{
		return $this->bindAndReturn(
			ActivationRepository::class,
			$this->repositories->createActivationRepository($context)
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function createReminderRepository($context, UserRepository $userRepository)
	{
		return $this->bindAndReturn(
			ReminderRepository::class,
			$this->repositories->createReminderRepository($context, $userRepository)
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function createPermissionRepository($context)
	{
		return $this->bindAndReturn(
			PermissionRepository::class,
			$this->repositories->createPermissionRepository($context)
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function createThrottleRepository($context)
	{
		return $this->bindAndReturn(
			ThrottleRepository::class,
			$this->repositories->createThrottleRepository($context)
		);
	}

	/**
	 * @param string $abstract
	 * @param object $instance
	 *
	 * @return object
	 */
	private function bindAndReturn($abstract, $instance)
	{
		$this->container->instance($abstract, $instance);

		return $instance;
	}
}
