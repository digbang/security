<?php namespace Digbang\Security\Factories;

use Digbang\Security\Activations\ActivationRepository;
use Digbang\Security\Configurations\SecurityContextConfiguration;
use Digbang\Security\Permissions\InsecurePermissionRepository;
use Digbang\Security\Persistences\PersistenceRepository;
use Digbang\Security\Reminders\ReminderRepository;
use Digbang\Security\Roles\NullRoleRepository;
use Digbang\Security\Roles\RoleRepository;
use Digbang\Security\SecurityContext;
use Digbang\Security\Throttling\ThrottleRepository;
use Digbang\Security\Users\UserRepository;
use Illuminate\Contracts\Container\Container;

class ConfigurationRepositoryFactory implements RepositoryFactory
{
	/**
	 * @var RepositoryFactory
	 */
	private $defaults;

	/**
	 * @var Container
	 */
	private $container;

	/**
	 * @var SecurityContextConfiguration
	 */
	private $config;

	/**
	 * ConfigurationRepositoryFactory constructor.
	 *
	 * @param Container         $container
	 * @param RepositoryFactory $defaults
	 */
	public function __construct(Container $container, RepositoryFactory $defaults)
	{
		$this->container = $container;
		$this->defaults  = $defaults;
	}

	/**
	 * {@inheritdoc}
	 */
	public function createPersistenceRepository($context)
	{
		if ($this->configuration($context)->getPersistenceRepository())
		{
			$repository = $this->container->make($this->configuration($context)->getPersistenceRepository());
		}
		else
		{
			$repository = $this->defaults->createPersistenceRepository($context);
		}

		$repository->setPersistenceMode($this->configuration($context)->isSinglePersistence() ? 'single' : 'multi');

		return $repository;
	}

	/**
	 * {@inheritdoc}
	 */
	public function createUserRepository($context, PersistenceRepository $persistenceRepository, RoleRepository $roleRepository)
	{
		if ($this->configuration($context)->getUserRepository())
		{
			return $this->container->make($this->configuration($context)->getUserRepository());
		}

		return $this->defaults->createUserRepository($context, $persistenceRepository, $roleRepository);
	}

	/**
	 * {@inheritdoc}
	 */
	public function createRoleRepository($context)
	{
		if (! $this->configuration($context)->isRolesEnabled())
		{
			return new NullRoleRepository;
		}

		if ($this->configuration($context)->getRoleRepository())
		{
			return $this->container->make($this->configuration($context)->getRoleRepository());
		}

		return $this->defaults->createRoleRepository($context);
	}

	/**
	 * {@inheritdoc}
	 */
	public function createActivationRepository($context)
	{
		if ($this->configuration($context)->getActivationRepository())
		{
			$repository = $this->container->make($this->configuration($context)->getActivationRepository());
		}
		else
		{
			$repository = $this->defaults->createActivationRepository($context);
		}

		/** @var ActivationRepository $repository */
		$repository->setExpires($this->configuration($context)->getActivationsExpiration());

		return $repository;
	}

	/**
	 * {@inheritdoc}
	 */
	public function createReminderRepository($context, UserRepository $userRepository)
	{
		if ($this->configuration($context)->getReminderRepository())
		{
			$repository = $this->container->make($this->configuration($context)->getReminderRepository());
		}
		else
		{
			$repository = $this->defaults->createReminderRepository($context, $userRepository);
		}

		/** @var ReminderRepository $repository */
		$repository->setExpires($this->configuration($context)->getRemindersExpiration());

		return $repository;
	}

	/**
	 * {@inheritdoc}
	 */
	public function createPermissionRepository($context)
	{
		$enabled = $this->configuration($context)->isPermissionsEnabled();

		if (! $enabled)
		{
			return new InsecurePermissionRepository;
		}

		if ($permissionRepository = $this->configuration($context)->getPermissionRepository())
		{
			return $this->container->make($permissionRepository);
		}

		return $this->defaults->createPermissionRepository($context);
	}

	/**
	 * {@inheritdoc}
	 */
	public function createThrottleRepository($context)
	{
		if ($this->configuration($context)->getThrottleRepository())
		{
			$repository = $this->container->make($this->configuration($context)->getThrottleRepository());
		}
		else
		{
			$repository = $this->defaults->createThrottleRepository($context);
		}

		/** @var ThrottleRepository $repository */
		$repository->setGlobalInterval($this->configuration($context)->getGlobalThrottleInterval());
		$repository->setGlobalThresholds($this->configuration($context)->getGlobalThrottleThresholds());
		$repository->setIpInterval($this->configuration($context)->getIpThrottleInterval());
		$repository->setIpThresholds($this->configuration($context)->getIpThrottleThresholds());
		$repository->setUserInterval($this->configuration($context)->getUserThrottleInterval());
		$repository->setUserThresholds($this->configuration($context)->getUserThrottleThresholds());

		return $repository;
	}

	/**
	 * @param string $context
	 * @return SecurityContextConfiguration
	 */
	private function configuration($context)
	{
		return $this->config ?: $this->config = $this->container->make(SecurityContext::class)->getConfigurationFor($context);
	}
}
