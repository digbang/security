<?php namespace Digbang\Security\Users;

use Carbon\Carbon;
use Cartalyst\Sentinel\Permissions\PermissionsInterface;
use Digbang\Doctrine\TimestampsTrait;
use Digbang\Security\Permissions\DefaultUserPermission;
use Digbang\Security\Permissions\NullPermissions;
use Digbang\Security\Permissions\Permissible;
use Digbang\Security\Roles\Role;
use Digbang\Security\Roles\Roleable;
use Doctrine\Common\Collections\ArrayCollection;

class DefaultUser implements User, Roleable, Permissible
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
		$this->permissionsFactory = function(){
			return new NullPermissions;
		};
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasAccess($permissions)
	{
		if (! $this->permissionsInstance)
		{
			$this->makePermissionsInstance();
		}

		return $this->permissionsInstance->hasAccess($permissions);
	}


	/**
	 * {@inheritdoc}
	 */
	public function hasAnyAccess($permissions)
	{
		return $this->permissionsInstance->hasAnyAccess($permissions);
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
			$this->email = new ValueObjects\Email($credentials['email']);
		}

		if (array_key_exists('username', $credentials))
		{
			$this->username = $credentials['username'];
		}

		if (array_key_exists('password', $credentials))
		{
			$this->password = new ValueObjects\Password($credentials['password']);
		}

		if (array_key_exists('firstName', $credentials) || array_key_exists('lastName', $credentials))
		{
			$firstName = array_get($credentials, 'firstName', $this->name->getFirstName());
			$lastName  = array_get($credentials, 'lastName',  $this->name->getLastName());

			$this->name = new ValueObjects\Name($firstName, $lastName);
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
		return $this->getEmail();
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
	 * @return Carbon
	 */
	public function getLastLogin()
	{
		return $this->lastLogin;
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
		if (! is_callable($this->permissionsFactory))
		{
			throw new \InvalidArgumentException("No PermissionFactory callable given. PermissionFactory callable should be set by the DoctrineUserRepository on instance creation. New instances will use a NullPermissions implementation until persisted.");
		}

		$permissionsFactory = $this->permissionsFactory;

		$secondary = $this->roles->map(function(Permissible $role){
			return $role->getPermissions();
		});

		$this->permissionsInstance = $permissionsFactory($this->permissions, $secondary->getValues());
	}

	/**
	 * {@inheritdoc}
	 */
	public function addRole(Role $role)
	{
		$this->roles->add($role);

		$this->makePermissionsInstance();
	}

	/**
	 * {@inheritdoc}
	 */
	public function removeRole(Role $role)
	{
		$this->roles->removeElement($role);

		$this->makePermissionsInstance();
	}

	/**
	 * @return string
	 */
	public function getEmail()
	{
		return $this->email->getAddress();
	}

	/**
	 * @return string
	 */
	public function getUsername()
	{
		return $this->username;
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
}
