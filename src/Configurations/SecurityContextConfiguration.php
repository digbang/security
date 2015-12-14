<?php namespace Digbang\Security\Configurations;

use Cartalyst\Sentinel\Checkpoints\ActivationCheckpoint;
use Cartalyst\Sentinel\Checkpoints\ThrottleCheckpoint;
use Digbang\Security\Mappings;
use Digbang\Security\Permissions\LazyStandardPermissions;
use Digbang\Security\Permissions\LazyStrictPermissions;

/**
 * Class SecurityContextConfiguration
 *
 * @package Digbang\Security\Configurations
 *
 * Mapping getters:
 * @method string getUserMapping()
 * @method string getActivationMapping()
 * @method string getUserPermissionMapping()
 * @method string getRolePermissionMapping()
 * @method string getPersistenceMapping()
 * @method string getReminderMapping()
 * @method string getRoleMapping()
 * @method string getThrottleMapping()
 * @method string getGlobalThrottleMapping()
 * @method string getIpThrottleMapping()
 * @method string getUserThrottleMapping()
 *
 * Module enablers:
 * @method static enableRoles()
 * @method static enablePermissions()
 * @method static disableRoles()
 * @method static disablePermissions()
 * @method bool isRolesEnabled()
 * @method bool isPermissionsEnabled()
 *
 * Throttle configuration getters - setters:
 * @method static setGlobalThrottleInterval($interval)
 * @method static setGlobalThrottleThresholds($thresholds)
 * @method static setIpThrottleInterval($interval)
 * @method static setIpThrottleThresholds($thresholds)
 * @method static setUserThrottleInterval($interval)
 * @method static setUserThrottleThresholds($thresholds)
 * @method int getGlobalThrottleInterval()
 * @method int|array getGlobalThrottleThresholds()
 * @method int getIpThrottleInterval()
 * @method int|array getIpThrottleThresholds()
 * @method int getUserThrottleInterval()
 * @method int|array getUserThrottleThresholds()
 *
 * Expiring getters - setters (reminders and activations):
 * @method static setRemindersExpiration(int $expiration)
 * @method static setRemindersLottery(array $lottery)
 * @method static setActivationsExpiration(int $expiration)
 * @method static setActivationsLottery(array $lottery)
 * @method int getRemindersExpiration()
 * @method array getRemindersLottery()
 * @method int getActivationsExpiration()
 * @method array getActivationsLottery()
 *
 * Repository getters:
 * @method null|string getUserRepository()
 * @method null|string getActivationRepository()
 * @method null|string getPersistenceRepository()
 * @method null|string getReminderRepository()
 * @method null|string getRoleRepository()
 * @method null|string getThrottleRepository()
 *
 * Table getters - setters:
 * @method static setUserTable(string $table)
 * @method static setUsersRolesTable(string $table)
 * @method static setUserPermissionTable(string $table)
 * @method static setRolePermissionTable(string $table)
 * @method static setActivationTable(string $table)
 * @method static setPersistenceTable(string $table)
 * @method static setReminderTable(string $table)
 * @method static setRoleTable(string $table)
 * @method static setThrottleTable(string $table)
 * @method string|null getUserTable()
 * @method string|null getUsersRolesTable()
 * @method string|null getUserPermissionTable()
 * @method string|null getRolePermissionTable()
 * @method string|null getActivationTable()
 * @method string|null getPersistenceTable()
 * @method string|null getReminderTable()
 * @method string|null getRoleTable()
 * @method string|null getThrottleTable()
 */
class SecurityContextConfiguration
{
	/**
	 * Mapping of each entity to its EntityMapping class or object.
	 * @var array
	 */
	private $mappings = [
		'user'           => Mappings\UserMapping::class,
		'activation'     => Mappings\ActivationMapping::class,
		'userPermission' => Mappings\UserPermissionMapping::class,
		'rolePermission' => Mappings\RolePermissionMapping::class,
		'persistence'    => Mappings\PersistenceMapping::class,
		'reminder'       => Mappings\ReminderMapping::class,
		'role'           => Mappings\RoleMapping::class,
		'throttle'       => Mappings\ThrottleMapping::class,
		'globalThrottle' => Mappings\GlobalThrottleMapping::class,
		'ipThrottle'     => Mappings\IpThrottleMapping::class,
		'userThrottle'   => Mappings\UserThrottleMapping::class,
	];

	/**
	 * Mapping of entities to custom repositories.
	 *
	 * @var array
	 */
	private $repositories = [
		'user'        => null,
		'activation'  => null,
		'persistence' => null,
		'reminder'    => null,
		'role'        => null,
		'throttle'    => null
	];

	/**
	 * Single persistence flag.
	 *
	 * @var bool
	 */
	private $singlePersistence = false;

	/**
	 * Modules that allow disabling.
	 *
	 * @var array
	 */
	private $enabled = [
		'roles'       => true,
		'permissions' => true
	];

	/**
	 * Permissions configuration.
	 * Permission classes have to implement Sentinel's PermissionInterface. Sentinel ships
	 * with two: StandardPermissions and StrictPermissions.
	 *
	 * The permission repository is used to retrieve permissions based on route resources.
	 * Security ships with two: InsecurePermissionRepository and RoutePermissionRepository.
	 * @var array
	 */
	private $permissions = [
		'factory'    => null,
		'repository' => null
	];

	/**
	 * Available checkpoints. Each checkpoint has to implement Sentinel's CheckpointInterface
	 * @var array
	 */
	private $checkpoints = [
		'throttle'   => ThrottleCheckpoint::class,
		'activation' => ActivationCheckpoint::class
	];

	/**
	 * Throttling configuration.
	 * Each throttling strategy can change the interval and set custom thresholds for
	 * each amount of retries.
	 *
	 * @var array
	 */
	private $throttles = [
		'global' => [
			'interval' => 900,
			'thresholds' => [
				10 => 1,
	            20 => 2,
	            30 => 4,
	            40 => 8,
	            50 => 16,
	            60 => 12
			]
		],
		'ip'     => [
			'interval' => 900,
			'thresholds' => 5
		],
		'user'   => [
			'interval' => 900,
			'thresholds' => 5
		]
	];

	/**
	 * Configuration of expiring modules: reminders and activations.
	 * Each of these modules may expire in a given time and has a lottery configuration,
	 * which Sentinel will use to sweep expired codes.
	 *
	 * @var array
	 */
	private $expiring = [
		'reminders' => [
			'expires' => 14400,
			'lottery' => [2,100]
		],
		'activations' => [
			'expires' => 259200,
			'lottery' => [2,100]
		]
	];

	/**
	 * Array of customized table names, one for each mapping.
	 *
	 * @var array
	 */
	private $customTables = [
		'usersRoles'     => 'user_role',
		'user'           => 'users',
		'userPermission' => 'user_permissions',
		'rolePermission' => 'role_permissions',
		'activation'     => 'activations',
		'persistence'    => 'persistences',
		'reminder'       => 'reminders',
		'role'           => 'roles',
		'throttle'       => 'throttles',
	];

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var string
	 */
	private $prefix;

	/**
	 * @var string
	 */
	private $loginRoute;

	/**
	 *
	 * SecurityContextConfiguration constructor.
	 *
	 * @param string $name
	 */
	public function __construct($name)
	{
		$this->name = $name;

		$this->setPrefix($name);
		$this->setStandardPermissions();
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getPrefix()
	{
		return substr($this->prefix, 0, -1);
	}

	/**
	 * Set the global table prefix. By default, the context name will be used, with
	 * a trailing underscore.
	 * A trailing underscore will be added to the given prefix automatically, if needed.
	 *
	 * To unset the global prefix, use an empty string as prefix.
	 *
	 * @param string $prefix
	 * @return $this
	 */
	public function setPrefix($prefix)
	{
		if ($prefix != '')
		{
			$prefix = rtrim($prefix, '_') . '_';
		}

		$this->prefix = $prefix;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getMappings()
	{
		$mappings = $this->mappings;

		if (! $this->isRolesEnabled())
		{
			unset($mappings['role']);
			unset($mappings['rolePermission']);
		}

		if (! $this->isThrottlesEnabled())
		{
			unset(
				$mappings['throttle'],
				$mappings['ipThrottle'],
				$mappings['globalThrottle'],
				$mappings['userThrottle']
			);
		}

		if (! $this->isPermissionsEnabled())
		{
			unset($mappings['userPermission']);
			unset($mappings['rolePermission']);
		}

		return $mappings;
	}

	/**
	 * Disable the throttling checkpoint
	 * @return $this
	 */
	public function disableThrottles()
	{
		unset($this->checkpoints['throttle']);

		return $this;
	}

	/**
	 * Enable throttling.
	 */
	public function enableThrottles()
	{
		$this->addCheckpoint('throttle', ThrottleCheckpoint::class);
	}

	/**
	 * @return bool
	 */
	public function isThrottlesEnabled()
	{
		return array_key_exists('throttle', $this->checkpoints);
	}

	/**
	 * @param string $key
	 * @param string $checkpoint Must implement \Cartalyst\Sentinel\Checkpoints\CheckpointInterface
	 * @return $this
	 */
	public function addCheckpoint($key, $checkpoint)
	{
		$this->checkpoints[$key] = $checkpoint;

		return $this;
	}

	/**
	 * @param string $key
	 * @return $this
	 */
	public function removeCheckpoint($key)
	{
		unset($this->checkpoints[$key]);

		return $this;
	}

	/**
	 * @return array
	 */
	public function listCheckpoints()
	{
		return $this->checkpoints;
	}

	/**
	 * Set a custom permissions factory. This closure will receive a Collection
	 * as first parameter and an array of Collection objects as second parameter,
	 * corresponding to user and role permissions.
	 * @param \Closure $factory
	 *
	 * @return $this
	 */
	public function setPermissionsFactory(\Closure $factory)
	{
		$this->permissions['factory'] = $factory;
		return $this;
	}

	/**
	 * @return \Closure
	 */
	public function getPermissionsFactory()
	{
		return $this->permissions['factory'];
	}

	/**
	 * Set the StandardPermissions mode. This means any user-specific permission will
	 * override role-based permissions.
	 *
	 * @return $this
	 */
	public function setStandardPermissions()
	{
		$this->permissions['factory'] = LazyStandardPermissions::getFactory();

		return $this;
	}

	/**
	 * Set the StrictPermissions mode. This means role-based permissions must allow access,
	 * even when a specific user-based permission allows it.
	 *
	 * @return $this
	 */
	public function setLazyPermissions()
	{
		$this->permissions['factory'] = LazyStrictPermissions::getFactory();

		return $this;
	}

	/**
	 * @param string $permissionRepository Must implement \Digbang\Security\Permissions\PermissionRepository
	 * @return $this
	 */
	public function setPermissionRepository($permissionRepository)
	{
		$this->permissions['repository'] = $permissionRepository;
		return $this;
	}

	/**
	 * @return string An FQCN that implements \Digbang\Security\Permissions\PermissionRepository
	 */
	public function getPermissionRepository()
	{
		return $this->permissions['repository'];
	}

	/**
	 * @return $this
	 */
	public function setSinglePersistence()
	{
		$this->singlePersistence = true;
		return $this;
	}

	/**
	 * @return $this
	 */
	public function setMultiplePersistence()
	{
		$this->singlePersistence = false;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isSinglePersistence()
	{
		return $this->singlePersistence;
	}

	/**
	 * @return bool
	 */
	public function isMultiplePersistence()
	{
		return ! $this->singlePersistence;
	}

	/**
	 * @param string $module
	 * @return $this
	 * @throws \InvalidArgumentException
	 */
	private function enable($module)
	{
		if (! array_key_exists($module, $this->enabled))
		{
			throw new \InvalidArgumentException("Module '$module' cannot be enabled or disabled. Only [" . implode(', ', array_keys($this->enabled)) . '] can.');
		}

		$this->enabled[$module] = true;

		return $this;
	}

	/**
	 * @param string $module
	 * @return $this
	 * @throws \InvalidArgumentException
	 */
	private function disable($module)
	{
		if (! array_key_exists($module, $this->enabled))
		{
			throw new \InvalidArgumentException("Module '$module' cannot be enabled or disabled. Only [" . implode(', ', array_keys($this->enabled)) . '] can.');
		}

		$this->enabled[$module] = false;

		return $this;
	}

	/**
	 * @param string $module
	 * @return bool
	 * @throws \InvalidArgumentException
	 */
	private function isEnabled($module)
	{
		if (! array_key_exists($module, $this->enabled))
		{
			throw new \InvalidArgumentException("Module '$module' cannot be enabled or disabled. Only [" . implode(', ', array_keys($this->enabled)) . '] can.');
		}

		return $this->enabled[$module];
	}

	/**
	 * @param string $entity
	 *
	 * @return string An FQCN that implements \LaravelDoctrine\Fluent\Mapping
	 * @throws \InvalidArgumentException
	 */
	private function getMapping($entity)
	{
		if (! array_key_exists($entity, $this->mappings))
		{
			throw new \InvalidArgumentException("'$entity' is not a valid mapping key. One of [" . implode(', ', array_keys($this->mappings)) . '] is expected.');
		}

		return $this->mappings[$entity];
	}

	/**
	 * @param string $type
	 * @param string $key
	 * @param int|array $value
	 *
	 * @return $this
	 */
	private function setThrottle($type, $key, $value)
	{
		if (! isset($this->throttles[$type][$key]))
		{
			throw new \InvalidArgumentException("Invalid throttle type or parameter given.");
		}

		$this->throttles[$type][$key] = $value;
		return $this;
	}

	/**
	 * @param string $type
	 * @param string $key
	 *
	 * @return int|array
	 */
	private function getThrottle($type, $key)
	{
		if (! isset($this->throttles[$type][$key]))
		{
			throw new \InvalidArgumentException("Invalid throttle type or parameter given.");
		}

		return $this->throttles[$type][$key];
	}

	/**
	 * @param string $type
	 * @param string $subtype
	 * @param int|array $value
	 *
	 * @return $this
	 */
	private function setExpiring($type, $subtype, $value)
	{
		if (! isset($this->expiring[$type][$subtype]))
		{
			throw new \InvalidArgumentException("Invalid type or parameter given.");
		}

		$this->expiring[$type][$subtype] = $value;

		return $this;
	}

	/**
	 * @param string $type
	 * @param string $subtype
	 *
	 * @return int|array
	 */
	private function getExpiring($type, $subtype)
	{
		if (! isset($this->expiring[$type][$subtype]))
		{
			throw new \InvalidArgumentException("Invalid type or parameter given.");
		}

		return $this->expiring[$type][$subtype];
	}

	/**
	 * @param string      $userRepository Must implement \Cartalyst\Sentinel\Users\UserRepositoryInterface
	 * @param string|null $userMapping Must implement \Digbang\Security\Mappings\SecurityUserMapping, null if
	 *                                 you want to keep the default user mapping.
	 *
	 * @return $this
	 */
	public function changeUsers($userRepository, $userMapping = null)
	{
		$this->repositories['user'] = $userRepository;
		$this->mappings['user']     = $userMapping ?: $this->mappings['user'];

		return $this;
	}

	/**
	 * @param string|null $userPermissionMapping Must implement \LaravelDoctrine\Fluent\EntityMapping
	 * @param string|null $rolePermissionMapping Must implement \LaravelDoctrine\Fluent\EntityMapping
	 */
	public function changePermissions($userPermissionMapping = null, $rolePermissionMapping = null)
	{
		$this->mappings['userPermission'] = $userPermissionMapping ?: $this->mappings['userPermission'];
		$this->mappings['rolePermission'] = $rolePermissionMapping ?: $this->mappings['rolePermission'];
	}

	/**
	 * @param string $activationRepository Must implement \Cartalyst\Sentinel\Activations\ActivationRepositoryInterface
	 * @param string|null $activationMapping Must implement \LaravelDoctrine\Fluent\EntityMapping
	 *
	 * @return $this
	 */
	public function changeActivations($activationRepository, $activationMapping = null)
	{
		$this->repositories['activation'] = $activationRepository;
		$this->mappings['activation']     = $activationMapping ?: $this->mappings['activation'];

		return $this;
	}

	/**
	 * @param string $persistenceRepository Must implement \Cartalyst\Sentinel\Persistences\PersistenceRepositoryInterface
	 * @param string|null $persistencesMapping Must implement \LaravelDoctrine\Fluent\EntityMapping
	 *
	 * @return $this
	 */
	public function changePersistences($persistenceRepository, $persistencesMapping = null)
	{
		$this->repositories['persistence'] = $persistenceRepository;
		$this->mappings['persistence']     = $persistencesMapping ?: $this->mappings['persistence'];
		return $this;
	}

	/**
	 * @param string $reminderRepository Must implement \Cartalyst\Sentinel\Reminders\ReminderRepositoryInterface
	 * @param string $reminderMappping Must implement \LaravelDoctrine\Fluent\EntityMapping
	 *
	 * @return $this
	 */
	public function changeReminders($reminderRepository, $reminderMappping = null)
	{
		$this->repositories['reminder'] = $reminderRepository;
		$this->mappings['reminder']     = $reminderMappping ?: $this->mappings['reminder'];
		return $this;
	}

	/**
	 * @param string $roleRepository Must implement \Cartalyst\Sentinel\Roles\RoleRepositoryInterface
	 * @param string $roleMapping Must implement \LaravelDoctrine\Fluent\EntityMapping
	 *
	 * @return $this
	 */
	public function changeRoles($roleRepository, $roleMapping = null)
	{
		$this->repositories['role'] = $roleRepository;
		$this->mappings['role']     = $roleMapping ?: $this->mappings['role'];
		return $this;
	}

	/**
	 * @param string $throttleRepository Must implement \Cartalyst\Sentinel\Throttling\ThrottleRepositoryInterface
	 * @param array  $throttleMappings You may set mappings for the following keys:
	 *                                 'throttle', 'ipThrottle', 'globalThrottle', 'userThrottle'
	 *                                 if not present, defaults will be used.
	 *
	 * @return $this
	 */
	public function changeThrottles($throttleRepository, array $throttleMappings = [])
	{
		$this->repositories['throttle'] = $throttleRepository;
		foreach(['throttle', 'ipThrottle', 'globalThrottle', 'userThrottle'] as $key)
		{
			$this->mappings[$key] = array_get($throttleMappings, $key, $this->mappings[$key]);
		}

		return $this;
	}

	/**
	 * @param $entity
	 * @return mixed
	 */
	private function getRepository($entity)
	{
		if (! array_key_exists($entity, $this->repositories))
		{
			throw new \InvalidArgumentException("'$entity' is not a valid repository. One of [" . implode(', ', array_keys($this->repositories)) . '] is expected.');
		}

		return $this->repositories[$entity];
	}

	/**
	 * @param string $entity
	 * @param string $table
	 *
	 * @return $this
	 */
	private function setMappingTable($entity, $table)
	{
		$this->customTables[$entity] = $table;

		return $this;
	}

	/**
	 * @param string $entity
	 *
	 * @return string|null
	 */
	public function getTable($entity)
	{
		if (array_key_exists($entity, $this->customTables))
		{
			return $this->prefix . $this->customTables[$entity];
		}

		return null;
	}

	/**
	 * is triggered when invoking inaccessible methods in an object context.
	 *
	 * @param $name      string
	 * @param $arguments array
	 *
	 * @return mixed
	 * @link http://php.net/manual/en/language.oop5.overloading.php#language.oop5.overloading.methods
	 */
	public function __call($name, $arguments)
	{
		if (preg_match('/^set(.*)Table$/', $name, $matches))
		{
			if (empty($arguments))
			{
				throw new \InvalidArgumentException("$name expects 1 parameter, none given.");
			}

			return $this->setMappingTable(lcfirst($matches[1]), array_shift($arguments));
		}

		if (preg_match('/^get(.*)Table/', $name, $matches))
		{
			return $this->getTable(lcfirst($matches[1]));
		}

		if (preg_match('/^get(.*)Mapping$/', $name, $matches))
		{
			return $this->getMapping(lcfirst($matches[1]));
		}

		if (preg_match('/^get(.*)Repository$/', $name, $matches))
		{
			return $this->getRepository(lcfirst($matches[1]));
		}

		if (preg_match('/^enable(.*)$/', $name, $matches))
		{
			return $this->enable(lcfirst($matches[1]));
		}

		if (preg_match('/^disable(.*)$/', $name, $matches))
		{
			return $this->disable(lcfirst($matches[1]));
		}

		if (preg_match('/^is(.*)Enabled$/', $name, $matches))
		{
			return $this->isEnabled(lcfirst($matches[1]));
		}

		if (preg_match('/^set(.*)Throttle(.*)$/', $name, $matches))
		{
			if (empty($arguments))
			{
				throw new \InvalidArgumentException("$name expects 1 parameter, none given.");
			}

			return $this->setThrottle(lcfirst($matches[1]), lcfirst($matches[2]), array_shift($arguments));
		}

		if (preg_match('/^get(.*)Throttle(.*)$/', $name, $matches))
		{
			return $this->getThrottle(lcfirst($matches[1]), lcfirst($matches[2]));
		}

		if (preg_match('/^set(.*)Expiration$/', $name, $matches))
		{
			if (empty($arguments))
			{
				throw new \InvalidArgumentException("$name expects 1 parameter, none given.");
			}

			return $this->setExpiring(lcfirst($matches[1]), 'expires', array_shift($arguments));
		}

		if (preg_match('/^set(.*)Lottery$/', $name, $matches))
		{
			if (empty($arguments))
			{
				throw new \InvalidArgumentException("$name expects 1 parameter, none given.");
			}

			return $this->setExpiring(lcfirst($matches[1]), 'lottery', array_shift($arguments));
		}

		if (preg_match('/^get(.*)Expiration$/', $name, $matches))
		{
			return $this->getExpiring(lcfirst($matches[1]), 'expires');
		}

		if (preg_match('/^get(.*)Lottery$/', $name, $matches))
		{
			return $this->getExpiring(lcfirst($matches[1]), 'lottery');
		}

		throw new \BadMethodCallException("Invalid method [$name].");
	}

	/**
	 * @return string
	 */
	public function getLoginRoute()
	{
		return $this->loginRoute;
	}

	/**
	 * @param string $loginRoute
	 */
	public function setLoginRoute($loginRoute)
	{
		$this->loginRoute = $loginRoute;
	}
}
