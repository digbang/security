<?php namespace Digbang\Security\Roles;

use Cartalyst\Sentinel\Permissions\PermissionsInterface;
use Digbang\Doctrine\TimestampsTrait;
use Digbang\Security\Permissions\DefaultRolePermission;
use Digbang\Security\Permissions\NullPermissions;
use Digbang\Security\Permissions\Permissible;
use Digbang\Security\Users\DefaultUser;
use Doctrine\Common\Collections\ArrayCollection;

class DefaultRole implements Role, Permissible
{
	use TimestampsTrait;

	/**
	 * Probably unused, but part of the sentinel interface...
	 *
	 * @type string
	 */
	private static $usersModel = DefaultUser::class;

	/**
	 * @type int
	 */
	private $id;

	/**
	 * @type string
	 */
	private $name;

	/**
	 * @type string
	 */
	private $slug;

	/**
	 * @type ArrayCollection
	 */
	private $permissions;

	/**
	 * @type ArrayCollection
	 */
	private $users;

	/**
	 * @type PermissionsInterface
	 */
	private $permissionsInstance;

	/**
	 * @type \Closure
	 */
	private $permissionsFactory;

	/**
	 * DefaultRole constructor.
	 *
	 * @param string      $name
	 * @param string|null $slug
	 */
	public function __construct($name, $slug = null)
	{
		$this->name = $name;
		$this->slug = $slug ?: str_slug($name);

		$this->permissions = new ArrayCollection;
		$this->users       = new ArrayCollection;

		$this->permissionsFactory = function(){
			return new NullPermissions;
		};
	}

	/**
	 * {@inheritdoc}
	 */
	public function getRoleId()
	{
		return $this->id;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getRoleSlug()
	{
		return $this->getSlug();
	}

	/**
	 * @return string
	 */
	public function getSlug()
	{
		return $this->slug;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getUsers()
	{
		return $this->users;
	}

	/**
	 * @return \Carbon\Carbon
	 */
	public function getCreatedAt()
	{
		return $this->createdAt;
	}

	/**
	 * @return \Carbon\Carbon
	 */
	public function getUpdatedAt()
	{
		return $this->updatedAt;
	}

	/**
	 * {@inheritdoc}
	 */
	public function is($role)
	{
		if ($role instanceof Role)
		{
			return $this->getRoleId() == $role->getRoleId();
		}

		return $this->getRoleSlug() == $role;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPermissions()
	{
		return $this->permissions;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function getUsersModel()
	{
		return static::$usersModel;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function setUsersModel($usersModel)
	{
		static::$usersModel = $usersModel;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setPermissionsFactory(\Closure $permissionsFactory)
	{
		$this->permissionsFactory = $permissionsFactory;

		$this->makePermissionsInstance();
	}

	/**
	 * @param string $permission
	 *
	 * @return bool
	 */
	public function hasAccess($permission)
	{
		if (! $this->permissionsInstance)
		{
			$this->makePermissionsInstance();
		}

		return $this->permissionsInstance->hasAccess($permission);
	}

	/**
	 * @param string $permission
	 *
	 * @return bool
	 */
	public function hasAnyAccess($permission)
	{
		if (! $this->permissionsInstance)
		{
			$this->makePermissionsInstance();
		}

		return $this->permissionsInstance->hasAnyAccess($permission);
	}

	/**
	 * Returns the permissions instance.
	 *
	 * @return \Cartalyst\Sentinel\Permissions\PermissionsInterface
	 */
	public function getPermissionsInstance()
	{
		return $this->permissionsInstance;
	}

	/**
	 * Adds a permission.
	 *
	 * @param  string $permission
	 * @param  bool   $value
	 *
	 * @return \Cartalyst\Sentinel\Permissions\PermissibleInterface
	 */
	public function addPermission($permission, $value = true)
	{
		return $this->updatePermission($permission, $value, true);
	}

	/**
	 * Updates a permission.
	 *
	 * @param  string $permission
	 * @param  bool   $value
	 * @param  bool   $create
	 *
	 * @return \Cartalyst\Sentinel\Permissions\PermissibleInterface
	 */
	public function updatePermission($permission, $value = true, $create = false)
	{
		if ($create || $this->permissions->containsKey($permission))
		{
			$this->permissions->set($permission, new DefaultRolePermission($this, $permission, $value));

			// Rebuild the permissions instance
			$this->makePermissionsInstance();
		}

		return $this;
	}

	/**
	 * Removes a permission.
	 *
	 * @param  string $permission
	 *
	 * @return \Cartalyst\Sentinel\Permissions\PermissibleInterface
	 */
	public function removePermission($permission)
	{
		$this->permissions->remove($permission);

		// Rebuild the permissions instance
		$this->makePermissionsInstance();

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	private function makePermissionsInstance()
	{
		if (! is_callable($this->permissionsFactory))
		{
			throw new \InvalidArgumentException("No PermissionFactory callable given. PermissionFactory callable should be set by the DoctrineUserRepository on instance creation. New instances will use a NullPermissions implementation until persisted.");
		}

		$permissionsFactory = $this->permissionsFactory;

		$this->permissionsInstance = $permissionsFactory(null, [$this->permissions]);
	}
}
