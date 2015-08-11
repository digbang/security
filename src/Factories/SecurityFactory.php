<?php namespace Digbang\Security\Factories;

use Cartalyst\Sentinel\Checkpoints\ActivationCheckpoint;
use Cartalyst\Sentinel\Checkpoints\CheckpointInterface;
use Cartalyst\Sentinel\Checkpoints\ThrottleCheckpoint;
use Cartalyst\Sentinel\Sentinel;
use Digbang\Security\Activations\ActivationRepository;
use Digbang\Security\Configurations\SecurityContextConfiguration;
use Digbang\Security\Persistences\PersistenceRepository;
use Digbang\Security\Reminders\ReminderRepository;
use Digbang\Security\Roles\NullRoleRepository;
use Digbang\Security\Roles\RoleRepository;
use Digbang\Security\Security;
use Digbang\Security\Users\UserRepository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\Response;

class SecurityFactory
{
	/**
	 * @type Container
	 */
	private $container;

	/**
	 * @type RepositoryFactory
	 */
	private $repositoryFactory;

	/**
	 * @param Container         $container
	 * @param RepositoryFactory $repositoryFactory
	 */
	public function __construct(Container $container, RepositoryFactory $repositoryFactory)
	{
		$this->container         = $container;
		$this->repositoryFactory = $repositoryFactory;
	}

	/**
	 * @param string $context
	 * @param SecurityContextConfiguration $configuration
	 * @return Security
	 */
	public function create($context, SecurityContextConfiguration $configuration)
	{
		$persistenceRepository = $this->getPersistenceRepository($context, $configuration);
		$userRepository = $this->getUserRepository($configuration, $persistenceRepository);

		$sentinel = new Sentinel(
			$persistenceRepository,
			$userRepository,
			$this->getRoleRepository($configuration),
			$this->getActivationRepository($configuration),
			$this->container->make(Dispatcher::class)
		);

		foreach ($configuration->listCheckpoints() as $key => $checkpoint)
		{
			$sentinel->addCheckpoint($key, $this->makeCheckpoint($checkpoint, $configuration));
		}

		$sentinel->setReminderRepository(
			$this->getReminderRepository($configuration, $userRepository)
		);

		$sentinel->setRequestCredentials(function(){
            $request = $this->container->make('request');

            $login    = $request->getUser();
            $password = $request->getPassword();

            if ($login === null && $password === null) {
                return;
            }

            return compact('login', 'password');
        });

		$sentinel->creatingBasicResponse(function () {
            $headers = ['WWW-Authenticate' => 'Basic'];

            return new Response('Invalid credentials.', 401, $headers);
        });

		$security = new Security(
			$sentinel,
			$configuration->isRolesEnabled(),
			$configuration->isPermissionsEnabled()
		);

		return $security;
	}

	/**
	 * @param string                       $context
	 * @param SecurityContextConfiguration $configuration
	 *
	 * @return PersistenceRepository
	 */
	private function getPersistenceRepository($context, SecurityContextConfiguration $configuration)
	{
		if ($configuration->getPersistenceRepository())
		{
			return $this->container->make($configuration->getPersistenceRepository());
		}

		return $this->repositoryFactory->createPersistenceRepository(
			$context,
			$configuration->isSinglePersistence()
		);
	}

	/**
	 * @param SecurityContextConfiguration $configuration
	 *
	 * @return RoleRepository
	 */
	private function getRoleRepository(SecurityContextConfiguration $configuration)
	{
		if (! $configuration->isRolesEnabled())
		{
			return new NullRoleRepository;
		}

		if ($configuration->getRoleRepository())
		{
			return $this->container->make($configuration->getRoleRepository());
		}

		return $this->repositoryFactory->createRoleRepository();
	}

	/**
	 * @param SecurityContextConfiguration $configuration
	 * @param PersistenceRepository        $persistenceRepository
	 *
	 * @return UserRepository
	 */
	private function getUserRepository(SecurityContextConfiguration $configuration, PersistenceRepository $persistenceRepository)
	{
		if ($configuration->getUserRepository())
		{
			return $this->container->make($configuration->getUserRepository());
		}

		return $this->repositoryFactory->createUserRepository($persistenceRepository, $configuration->getPermissionsFactory());
	}

	/**
	 * @param SecurityContextConfiguration $configuration
	 *
	 * @return ActivationRepository
	 */
	private function getActivationRepository(SecurityContextConfiguration $configuration)
	{
		if ($configuration->getActivationRepository())
		{
			return $this->container->make($configuration->getActivationRepository());
		}

		return $this->repositoryFactory->createActivationRepository(
			$configuration->getActivationsExpiration()
		);
	}

	/**
	 * @param SecurityContextConfiguration $configuration
	 * @param UserRepository               $userRepository
	 *
	 * @return ReminderRepository
	 */
	private function getReminderRepository(SecurityContextConfiguration $configuration, UserRepository $userRepository)
	{
		if ($configuration->getReminderRepository())
		{
			return $this->container->make($configuration->getReminderRepository());
		}

		return $this->repositoryFactory->createReminderRepository(
			$userRepository,
			$configuration->getRemindersExpiration()
		);
	}

	/**
	 * @param CheckpointInterface|string $checkpoint
	 * @param SecurityContextConfiguration $configuration
	 *
	 * @return ActivationCheckpoint|ThrottleCheckpoint
	 */
	private function makeCheckpoint($checkpoint, SecurityContextConfiguration $configuration)
	{
		switch ($checkpoint)
		{
			case ThrottleCheckpoint::class:
				return $this->makeThrottleCheckpoint($configuration);
			case ActivationCheckpoint::class:
				return $this->makeActivationCheckpoint($configuration);
			default:
				return $this->container->make($checkpoint);
		}
	}

	private function makeThrottleCheckpoint(SecurityContextConfiguration $configuration)
	{
		if ($configuration->getThrottleRepository())
		{
			$throttleRepository = $this->container->make($configuration->getThrottleRepository());
		}
		else
		{
			$throttleRepository = $this->repositoryFactory->createThrottleRepository(
				$configuration->getGlobalThrottleInterval(),
				$configuration->getGlobalThrottleThresholds(),
				$configuration->getIpThrottleInterval(),
				$configuration->getIpThrottleThresholds(),
				$configuration->getUserThrottleInterval(),
				$configuration->getUserThrottleThresholds()
			);
		}

		/** @type \Illuminate\Http\Request $request */
		$request = $this->container->make('request');

		return new ThrottleCheckpoint($throttleRepository, $request->getClientIp());
	}

	private function makeActivationCheckpoint(SecurityContextConfiguration $configuration)
	{
		if ($configuration->getActivationRepository())
		{
			$activationRepository = $this->container->make($configuration->getActivationRepository());
		}
		else
		{
			$activationRepository = $this->repositoryFactory->createActivationRepository(
				$configuration->getActivationsExpiration()
			);
		}

		return new ActivationCheckpoint($activationRepository);
	}
}
