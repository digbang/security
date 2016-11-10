<?php namespace Digbang\Security\Users;

use Carbon\Carbon;
use Digbang\Security\Support\TimestampsTrait;
use Digbang\Security\Activations\Activation;
use Digbang\Security\Permissions\DefaultUserPermission;
use Digbang\Security\Permissions\NullPermissions;
use Digbang\Security\Permissions\Permissible;
use Digbang\Security\Permissions\PermissibleTrait;
use Digbang\Security\Permissions\Permission;
use Digbang\Security\Persistences\Persistable;
use Digbang\Security\Persistences\PersistableTrait;
use Digbang\Security\Roles\Role;
use Digbang\Security\Roles\Roleable;
use Digbang\Security\Roles\RoleableTrait;
use Digbang\Security\Throttling\Throttleable;
use Digbang\Security\Throttling\ThrottleableTrait;
use Doctrine\Common\Collections\ArrayCollection;

class DefaultUser implements User, Roleable, Permissible, Persistable, Throttleable
{
	use TimestampsTrait;
	use PersistableTrait;
	use PermissibleTrait;
	use ThrottleableTrait;
	use RoleableTrait {
		addRole    as _addRole;
		removeRole as _removeRole;
	}

	/**
	 * @var int
	 */
	private $id;

	/**
	 * @var ValueObjects\Email
	 */
	private $email;

	/**
	 * @var string
	 */
	private $username;

	/**
	 * @var ValueObjects\Password
	 */
	private $password;

	/**
	 * @var ValueObjects\Name
	 */
	private $name;

	/**
	 * @var Carbon
	 */
	private $lastLogin;

	/**
	 * @var ArrayCollection
	 */
	private $activations;

	/**
	 * @var ArrayCollection
	 */
	private $reminders;

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

		$this->roles        = new ArrayCollection;
		$this->permissions  = new ArrayCollection;
		$this->persistences = new ArrayCollection;
		$this->activations  = new ArrayCollection;
		$this->reminders    = new ArrayCollection;
		$this->throttles    = new ArrayCollection;
		$this->name         = new ValueObjects\Name;
		$this->permissionsFactory = function(){
			return new NullPermissions;
		};
	}

	protected function createPermission($permission, $value)
	{
		return new DefaultUserPermission($this, $permission, $value);
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
	 * @return string
	 */
	public function getFirstName()
	{
		return $this->name->getFirstName();
	}

	/**
	 * @return string
	 */
	public function getLastName()
	{
		return $this->name->getLastName();
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

		if (array_key_exists('password', $credentials) && !empty($credentials['password']))
		{
			$this->password = new ValueObjects\Password($credentials['password']);
		}

		if (array_key_exists('firstName', $credentials) || array_key_exists('lastName', $credentials))
		{
			$firstName = array_get($credentials, 'firstName', $this->name->getFirstName());
			$lastName  = array_get($credentials, 'lastName',  $this->name->getLastName());

			$this->name = new ValueObjects\Name($firstName, $lastName);
		}

		if (array_key_exists('permissions', $credentials))
		{
			$this->syncPermissions((array) $credentials['permissions']);
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
	public function checkPassword($password)
	{
		return $this->password->check($password);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPersistableId()
	{
		return $this->getUserId();
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
	public function recordLogin()
	{
		$this->lastLogin = Carbon::now();
	}

	/**
	 * {@inheritdoc}
	 */
	protected function makePermissionsInstance()
	{
		$permissionsFactory = $this->getPermissionsFactory();

		if (! is_callable($permissionsFactory))
		{
			throw new \InvalidArgumentException("No PermissionFactory callable given. PermissionFactory callable should be set by the DoctrineUserRepository on instance creation. New instances will use a NullPermissions implementation until persisted.");
		}

		$secondary = $this->roles->map(function(Permissible $role){
			return $role->getPermissions();
		});

		return $permissionsFactory($this->permissions, $secondary->getValues());
	}

	/**
	 * {@inheritdoc}
	 */
	public function syncPermissions(array $permissions)
	{
		foreach ($this->permissions as $current)
		{
			/** @var Permission $current */
			if ($current->isAllowed() && ! in_array($current->getName(), $permissions))
			{
				$current->deny();
			}
			elseif (! $current->isAllowed() && in_array($current->getName(), $permissions))
			{
				$current->allow();
			}
		}

		$this->roles->map(function(Role $role) use ($permissions) {
			if ($role instanceof Permissible)
			{
				$rolePermissions = $role->getPermissions();
				$rolePermissions
					->filter(function(Permission $permission) use ($permissions) {
						return $permission->isAllowed() && ! in_array($permission->getName(), $permissions);
					})
					->map(function(Permission $permission){
						$this->addPermission($permission->getName(), false);
					});

				$rolePermissions->map(function(Permission $permission){
					$this->permissions->filter(function(Permission $current) use ($permission){
						return $current->equals($permission);
					})->map(function(Permission $repeated){
						$this->permissions->removeElement($repeated);
					});
				});
			}
		});

		$this->refreshPermissionsInstance();

		$this->allow($permissions);
	}

	/**
	 * {@inheritdoc}
	 */
	public function addRole(Role $role)
	{
		$this->_addRole($role);

		$this->refreshPermissionsInstance();
	}

	/**
	 * {@inheritdoc}
	 */
	public function removeRole(Role $role)
	{
		$this->_removeRole($role);

		$this->refreshPermissionsInstance();
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

	/**
	 * @return ArrayCollection
	 */
	public function getActivations()
	{
		return $this->activations;
	}

	/**
	 * @return ArrayCollection
	 */
	public function getReminders()
	{
		return $this->reminders;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isActivated()
	{
		return $this->activations->exists(function($id, Activation $activation){
			return $activation->isCompleted();
		});
	}

	/**
	 * {@inheritdoc}
	 */
	public function getActivatedAt()
	{
		$completed = $this->activations->filter(function(Activation $activation){
			return $activation->isCompleted();
		});

		if ($completed->isEmpty())
		{
			return null;
		}

		return $completed->first()->getCompletedAt();
	}
}
