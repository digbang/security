<?php namespace Digbang\Security\Users;

use Carbon\Carbon;
use Cartalyst\Sentinel\Permissions\PermissionsInterface;
use Cartalyst\Sentinel\Roles\RoleableInterface;
use Digbang\Doctrine\TimestampsTrait;
use Digbang\Security\Permissions\DefaultUserPermission;
use Digbang\Security\Permissions\Permissible;
use Digbang\Security\Roles\Role;
use Digbang\Security\Users\ValueObjects\Email;
use Digbang\Security\Users\ValueObjects\Name;
use Digbang\Security\Users\ValueObjects\Password;
use Doctrine\Common\Collections\ArrayCollection;

class DefaultUser implements User, RoleableInterface, Permissible
{
	use TimestampsTrait;

	/**
	 * @type int
	 */
	private $id;

	/**
	 * @type ValueObjects\Email
	 */
	private $email;

	/**
	 * @type string
	 */
	private $username;

	/**
	 * @type ValueObjects\Password
	 */
	private $password;

	/**
	 * @type ValueObjects\Name
	 */
	private $name;

	/**
	 * @type Carbon
	 */
	private $lastLogin;

	/**
	 * @type ArrayCollection
	 */
	private $roles;

	/**
	 * @type ArrayCollection
	 */
	private $permissions;

	/**
	 * @type PermissionsInterface
	 */
	private $permissionsInstance;

	/**
	 * @type \Closure
	 */
	private $permissionsFactory;

	/**
	 * @param ValueObjects\Email    $email
	 * @param ValueObjects\Password $password
	 * @param string                $username
	 */
	public function __construct(ValueObjects\Email $email, ValueObjects\Password $password, $username)
	{
		$this->email    = $email;
		$this->username = $username;
		$this->password = $password;

		$this->roles       = new ArrayCollection;
		$this->permissions = new ArrayCollection;
		$this->name        = new ValueObjects\Name;
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasAccess($permissions, $all = true)
	{
		if ($all)
		{
			return $this->permissionsInstance->hasAccess($permissions);
		}

		return $this->permissionsInstance->hasAnyAccess($permissions);
	}


	/**
	 * {@inheritdoc}
	 */
	public function hasAnyAccess($permissions)
	{
		return $this->hasAccess($permissions, false);
	}

	/**
	 * @param ValueObjects\Name $name
	 */
	public function setName(ValueObjects\Name $name)
	{
		$this->name = $name;
	}

	/**
	 * @return ValueObjects\Name
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws \InvalidArgumentException
	 */
	public function update(array $credentials)
	{
		if (array_key_exists('email', $credentials))
		{
			$this->email = new Email($credentials['email']);
		}

		if (array_key_exists('username', $credentials))
		{
			$this->username = $credentials['username'];
		}

		if (array_key_exists('password', $credentials))
		{
			$this->password = new Password($credentials['password']);
		}

		if (array_key_exists('firstName', $credentials) || array_key_exists('lastName', $credentials))
		{
			$firstName = array_get($credentials, 'firstName', $this->name->getFirstName());
			$lastName = array_get($credentials,  'lastName',  $this->name->getLastName());

			$this->name = new Name($firstName, $lastName);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getUserId()
	{
		return $this->id;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getUserLogin()
	{
		return $this->email->getAddress();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getUserLoginName()
	{
		return 'email';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getUserPassword()
	{
		return $this->password->getHash();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getRoles()
	{
		return $this->roles;
	}

	/**
	 * {@inheritdoc}
	 */
	public function inRole($role)
	{
		return $this->roles->exists(function($key, Role $myRole) use ($role){
			return $myRole->is($role);
		});
	}

	/**
	 * {@inheritdoc}
	 */
	public function checkPassword($password)
	{
		return $this->password->check($password);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPermissionsInstance()
	{
		return $this->permissionsInstance;
	}

	/**
	 * {@inheritdoc}
	 */
	public function addPermission($permission, $value = true)
	{
		return $this->updatePermission($permission, $value, true);
	}

	/**
	 * {@inheritdoc}
	 */
	public function updatePermission($permission, $value = true, $create = false)
	{
		if ($create || $this->permissions->containsKey($permission))
		{
			$this->permissions->set($permission, new DefaultUserPermission($this, $permission, $value));

			// Rebuild the permissions instance
			$this->makePermissionsInstance();
		}

		return $this;
	}

	/**
	 * {@inheritdoc}
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
	public function getPersistableKey()
	{
		return 'user_id';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPersistableId()
	{
		return $this->id;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPersistableRelationship()
	{
		return 'persistences';
	}

	/**
	 * {@inheritdoc}
	 */
	public function generatePersistenceCode()
	{
		return str_random(32);
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
	public function recordLogin()
	{
		$this->lastLogin = Carbon::now();
	}

	/**
	 * {@inheritdoc}
	 */
	public function setPermissionsFactory(\Closure $permissionsFactory)
	{
		$this->permissionsFactory = $permissionsFactory;

		// Make the permissions instance the first time.
		$this->makePermissionsInstance();
	}

		/**
	 * {@inheritdoc}
	 */
	private function makePermissionsInstance()
	{
		if (! $this->permissionsFactory)
		{
			throw new \InvalidArgumentException("No PermissionFactory callable given. PermissionFactory callable is set by the DoctrineUserRepository on instance creation.");
		}

		$permissionsFactory = $this->permissionsFactory;

		$secondary = $this->roles->map(function(Permissible $role){
			return $role->getPermissions();
		});

		$this->permissionsInstance = $permissionsFactory($this->permissions, $secondary->getValues());
	}
}
