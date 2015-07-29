<?php namespace Digbang\Security\Factories;

use Cartalyst\Sentinel\Activations\ActivationRepositoryInterface;
use Cartalyst\Sentinel\Cookies\IlluminateCookie;
use Cartalyst\Sentinel\Hashing\HasherInterface;
use Cartalyst\Sentinel\Persistences\PersistenceRepositoryInterface;
use Cartalyst\Sentinel\Reminders\ReminderRepositoryInterface;
use Cartalyst\Sentinel\Roles\RoleRepositoryInterface;
use Cartalyst\Sentinel\Sessions\IlluminateSession;
use Cartalyst\Sentinel\Throttling\ThrottleRepositoryInterface;
use Cartalyst\Sentinel\Users\UserRepositoryInterface;
use Digbang\Security\Activations\DefaultDoctrineActivationRepository;
use Digbang\Security\Contracts\Factories\RepositoryFactory;
use Digbang\Security\Permissions\PermissionRepository;
use Digbang\Security\Persistences\DefaultDoctrinePersistenceRepository;
use Digbang\Security\Reminders\DefaultDoctrineReminderRepository;
use Digbang\Security\Roles\DefaultDoctrineRoleRepository;
use Digbang\Security\Users\DefaultDoctrineUserRepository;
use Doctrine\ORM\EntityManager;
use Illuminate\Contracts\Container\Container;
use Illuminate\Cookie\CookieJar;
use Illuminate\Http\Request;
use Illuminate\Session\Store;

final class DefaultRepositoryFactory implements RepositoryFactory
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
	 * @param string $context
	 * @param bool $single
	 *
	 * @return PersistenceRepositoryInterface
	 */
	public function createPersistenceRepository($context, $single = false)
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

		return $this->instances['persistence'] = new DefaultDoctrinePersistenceRepository($entityManager, $session, $cookie, $single);
	}

	/**
	 * @param PersistenceRepositoryInterface $persistenceRepository
	 * @return UserRepositoryInterface
	 */
	public function createUserRepository(PersistenceRepositoryInterface $persistenceRepository)
	{
		if (array_key_exists('user', $this->instances))
		{
			return $this->instances['user'];
		}

		$entityManager = $this->container->make(EntityManager::class);
		$hasher = $this->container->make(HasherInterface::class);

		return $this->instances['user'] = new DefaultDoctrineUserRepository(
			$entityManager, $hasher, $persistenceRepository
		);
	}

	/**
	 * @return RoleRepositoryInterface
	 */
	public function createRoleRepository()
	{
		if (array_key_exists('role', $this->instances))
		{
			return $this->instances['role'];
		}

		$entityManager = $this->container->make(EntityManager::class);
		return $this->instances['role'] = new DefaultDoctrineRoleRepository($entityManager);
	}

	/**
	 * @param int $expires
	 * @return ActivationRepositoryInterface
	 */
	public function createActivationRepository($expires)
	{
		if (array_key_exists('activation', $this->instances))
		{
			return $this->instances['activation'];
		}

		$entityManager = $this->container->make(EntityManager::class);
		return $this->instances['activation'] = new DefaultDoctrineActivationRepository(
			$entityManager,
			$expires
		);
	}

	/**
	 * @param int $expires
	 * @return ReminderRepositoryInterface
	 */
	public function createReminderRepository($expires)
	{
		if (array_key_exists('reminder', $this->instances))
		{
			return $this->instances['reminder'];
		}

		$entityManager = $this->container->make(EntityManager::class);
		return $this->instances['reminder'] = new DefaultDoctrineReminderRepository(
			$entityManager,
			$expires
		);
	}

	/**
	 * @return PermissionRepository
	 */
	public function createPermissionRepository()
	{
		// TODO: Implement createPermissionRepository() method.
	}

	/**
	 * @return ThrottleRepositoryInterface
	 */
	public function createThrottleRepository()
	{
		// TODO: Implement createThrottleRepository() method.
	}
}
