<?php namespace Digbang\Security\Factories;

use Cartalyst\Sentinel\Persistences\PersistenceRepositoryInterface;
use Cartalyst\Sentinel\Sentinel;
use Digbang\Security\Configurations\SecurityContextConfiguration;
use Digbang\Security\Contracts\Factories\RepositoryFactory;
use Digbang\Security\Roles\NullRoleRepository;
use Digbang\Security\Security;
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

		$sentinel = new Sentinel(
			$persistenceRepository,
			$this->getUserRepository($configuration, $persistenceRepository),
			$this->getRoleRepository($configuration),
			$this->getActivationRepository($configuration),
			$this->container->make(Dispatcher::class)
		);

		foreach ($configuration->listCheckpoints() as $key => $checkpoint)
		{
			$sentinel->addCheckpoint($key, $checkpoint);
		}

		$sentinel->setReminderRepository(
			$this->getReminderRepository($configuration)
		);

		$sentinel->setRequestCredentials(function(){
            $request = $this->container->make('request');

            $login = $request->getUser();
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
	 * @return \Cartalyst\Sentinel\Persistences\PersistenceRepositoryInterface
	 */
	private function getPersistenceRepository($context, SecurityContextConfiguration $configuration)
	{
		return $configuration->getPersistenceRepository() ?:
			$this->repositoryFactory->createPersistenceRepository(
				$context,
				$configuration->isSinglePersistence()
			);
	}

	/**
	 * @param SecurityContextConfiguration $configuration
	 *
	 * @return \Cartalyst\Sentinel\Roles\RoleRepositoryInterface|NullRoleRepository
	 */
	private function getRoleRepository(SecurityContextConfiguration $configuration)
	{
		if (! $configuration->isRolesEnabled())
		{
			return new NullRoleRepository;
		}

		return $configuration->getRoleRepository() ?: $this->repositoryFactory->createRoleRepository();
	}

	/**
	 * @param SecurityContextConfiguration   $configuration
	 * @param PersistenceRepositoryInterface $persistenceRepository
	 *
	 * @return \Cartalyst\Sentinel\Users\UserRepositoryInterface
	 */
	private function getUserRepository(SecurityContextConfiguration $configuration, PersistenceRepositoryInterface $persistenceRepository)
	{
		return $configuration->getUserRepository() ?: $this->repositoryFactory->createUserRepository($persistenceRepository);
	}

	/**
	 * @param SecurityContextConfiguration $configuration
	 *
	 * @return \Cartalyst\Sentinel\Activations\ActivationRepositoryInterface
	 */
	private function getActivationRepository(SecurityContextConfiguration $configuration)
	{
		return $configuration->getActivationRepository() ?: $this->repositoryFactory->createActivationRepository(
			$configuration->getActivationsExpiration()
		);
	}

	/**
	 * @param SecurityContextConfiguration $configuration
	 * @return \Cartalyst\Sentinel\Reminders\ReminderRepositoryInterface
	 */
	private function getReminderRepository(SecurityContextConfiguration $configuration)
	{
		return $configuration->getReminderRepository() ?: $this->repositoryFactory->createReminderRepository(
			$configuration->getRemindersExpiration()
		);
	}
}
