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
use Digbang\Security\Permissions\LazyStandardPermissions;
use Digbang\Security\Permissions\PermissionRepository;
use Digbang\Security\Persistences\DefaultDoctrinePersistenceRepository;
use Digbang\Security\Reminders\DefaultDoctrineReminderRepository;
use Digbang\Security\Roles\DefaultDoctrineRoleRepository;
use Digbang\Security\Throttling\DefaultDoctrineThrottleRepository;
use Digbang\Security\Users\DefaultDoctrineUserRepository;
use Doctrine\Common\Collections\Collection;
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
	 * {@inheritdoc]
	 */
	public function createUserRepository(PersistenceRepositoryInterface $persistenceRepository, \Closure $permissionsFactory = null)
	{
		if (array_key_exists('user', $this->instances))
		{
			return $this->instances['user'];
		}

		if (! $permissionsFactory)
		{
			$permissionsFactory = LazyStandardPermissions::getFactory();
		}

		$entityManager = $this->container->make(EntityManager::class);

		return $this->instances['user'] = new DefaultDoctrineUserRepository(
			$entityManager, $persistenceRepository, $permissionsFactory
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
	 * @param int       $globalInterval
	 * @param int|array $globalThresholds
	 * @param int       $ipInterval
	 * @param int|array $ipThresholds
	 * @param int       $userInterval
	 * @param int|array $userThresholds
	 *
	 * @return ThrottleRepositoryInterface
	 */
	public function createThrottleRepository(
		$globalInterval,
		$globalThresholds,
		$ipInterval,
		$ipThresholds,
		$userInterval,
		$userThresholds
	)
	{
		if (array_key_exists('throttle', $this->instances))
		{
			return $this->instances['throttle'];
		}

		$entityManager = $this->container->make(EntityManager::class);

		$repo = new DefaultDoctrineThrottleRepository(
			$entityManager
		);

		$repo->setGlobalInterval($globalInterval);
		$repo->setGlobalThresholds($globalThresholds);
		$repo->setIpInterval($ipInterval);
		$repo->setIpThresholds($ipThresholds);
		$repo->setUserInterval($userInterval);
		$repo->setUserThresholds($userThresholds);

		return $this->instances['throttle'] = $repo;
	}
}
