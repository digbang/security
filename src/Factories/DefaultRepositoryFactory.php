<?php namespace Digbang\Security\Factories;

use Cartalyst\Sentinel\Cookies\IlluminateCookie;
use Cartalyst\Sentinel\Sessions\IlluminateSession;
use Digbang\Security\Activations\DefaultDoctrineActivationRepository;
use Digbang\Security\Permissions\InsecurePermissionRepository;
use Digbang\Security\Permissions\LazyStandardPermissions;
use Digbang\Security\Permissions\RoutePermissionRepository;
use Digbang\Security\Persistences\DefaultDoctrinePersistenceRepository;
use Digbang\Security\Persistences\PersistenceRepository;
use Digbang\Security\Reminders\DefaultDoctrineReminderRepository;
use Digbang\Security\Roles\DefaultDoctrineRoleRepository;
use Digbang\Security\Throttling\DefaultDoctrineThrottleRepository;
use Digbang\Security\Users\DefaultDoctrineUserRepository;
use Digbang\Security\Users\UserRepository;
use Doctrine\ORM\EntityManager;
use Illuminate\Contracts\Container\Container;
use Illuminate\Cookie\CookieJar;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Session\Store;

class DefaultRepositoryFactory implements RepositoryFactory
{
	/**
	 * @type Container
	 */
	private $container;

	/**
	 * @type array
	 */
	private $instances = [];

	/**
	 * DefaultRepositoryFactory constructor.
	 * @param Container $container
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	/**
	 * {@inheritdoc}
	 */
	public function createPersistenceRepository($context)
	{
		if (array_key_exists('persistence', $this->instances))
		{
			return $this->instances['persistence'];
		}

		$entityManager = $this->container->make(EntityManager::class);
		$session = new IlluminateSession($this->container->make(Store::class), $context);
		$cookie = new IlluminateCookie(
			$this->container->make(Request::class),
			$this->container->make(CookieJar::class),
			$context
		);

		return $this->instances['persistence'] = new DefaultDoctrinePersistenceRepository($entityManager, $session, $cookie);
	}

	/**
	 * {@inheritdoc]
	 */
	public function createUserRepository($context, PersistenceRepository $persistenceRepository)
	{
		if (array_key_exists('user', $this->instances))
		{
			return $this->instances['user'];
		}

		$entityManager = $this->container->make(EntityManager::class);

		return $this->instances['user'] = new DefaultDoctrineUserRepository(
			$entityManager, $persistenceRepository
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function createRoleRepository($context)
	{
		if (array_key_exists('role', $this->instances))
		{
			return $this->instances['role'];
		}

		$entityManager = $this->container->make(EntityManager::class);
		return $this->instances['role'] = new DefaultDoctrineRoleRepository($entityManager);
	}

	/**
	 * {@inheritdoc}
	 */
	public function createActivationRepository($context)
	{
		if (array_key_exists('activation', $this->instances))
		{
			return $this->instances['activation'];
		}

		$entityManager = $this->container->make(EntityManager::class);
		return $this->instances['activation'] = new DefaultDoctrineActivationRepository($entityManager);
	}

	/**
	 * {@inheritdoc}
	 */
	public function createReminderRepository($context, UserRepository $userRepository)
	{
		if (array_key_exists('reminder', $this->instances))
		{
			return $this->instances['reminder'];
		}

		$entityManager = $this->container->make(EntityManager::class);
		return $this->instances['reminder'] = new DefaultDoctrineReminderRepository(
			$entityManager,
			$userRepository
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function createPermissionRepository($context)
	{
		return new RoutePermissionRepository($this->container->make(Router::class));
	}

	/**
	 * {@inheritdoc}
	 */
	public function createThrottleRepository($context)
	{
		if (array_key_exists('throttle', $this->instances))
		{
			return $this->instances['throttle'];
		}

		$entityManager = $this->container->make(EntityManager::class);

		return $this->instances['throttle'] = new DefaultDoctrineThrottleRepository($entityManager);
	}
}
