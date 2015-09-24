<?php namespace Digbang\Security\Permissions;

use Cartalyst\Sentinel\Permissions\PermissionsInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Trait PermissibleTrait implements \Digbang\Security\Permissions\Permissible
 *
 * @package Digbang\Security\Permissions
 */
trait PermissibleTrait
{
	/**
	 * @type ArrayCollection
	 */
	protected $permissions;

	/**
	 * @type PermissionsInterface
	 */
	private $permissionsInstance;

	/**
	 * @type \Closure
	 */
	protected $permissionsFactory;

	/**
	 * @return PermissionsInterface
	 */
	abstract protected function makePermissionsInstance();

	/**
	 * @param string $permission
	 * @param bool   $value
	 *
	 * @return Permission
	 */
	abstract protected function createPermission($permission, $value);

	/**
	 * {@inheritdoc}
	 */
	public function hasAccess($permissions)
	{
		return $this->getPermissionsInstance()->hasAccess($permissions);
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasAnyAccess($permissions)
	{
		return $this->getPermissionsInstance()->hasAnyAccess($permissions);
	}

	/**
	 * {@inheritdoc}
	 */
	public function allow($permissions, $force = false)
	{
		foreach ((array) $permissions as $permission)
		{
			if ($force || !$this->hasAccess($permission))
			{
				$this->addPermission($permission);
			}
		}

		$this->refreshPermissionsInstance();
	}

	/**
	 * {@inheritdoc}
	 */
	public function deny($permissions, $force = false)
	{
		foreach ((array) $permissions as $permission)
		{
			if ($force || $this->hasAccess($permission))
			{
				$this->addPermission($permission, false);
			}
		}

		$this->refreshPermissionsInstance();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPermissionsInstance()
	{
		if (! $this->permissionsInstance)
		{
			$this->refreshPermissionsInstance();
		}

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
		$existing = $this->getPermission($permission);

		if ($create || $existing)
		{
			if ($existing)
			{
				$this->removePermission($existing);
			}

			$this->permissions->add($this->createPermission($permission, $value));

			$this->refreshPermissionsInstance();
		}

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function removePermission($permission)
	{
		if ($object = $this->getPermission($permission))
		{
			$this->permissions->removeElement($object);
			$this->refreshPermissionsInstance();
		}

		return $this;
	}

	/**
	 * @param string $permission
	 *
	 * @return Permission|null
	 */
	protected function getPermission($permission)
	{
		return $this->permissions->filter(function(Permission $object) use ($permission) {
			return $object->getName() == $permission;
		})->first();
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
	public function setPermissionsFactory(\Closure $permissionsFactory)
	{
		$this->permissionsFactory = $permissionsFactory;

		$this->refreshPermissionsInstance();
	}

	/**
	 * {@inheritdoc}
	 */
	public function clearPermissions()
	{
		$this->permissions->clear();

		$this->refreshPermissionsInstance();
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getPermissionsFactory()
	{
		return $this->permissionsFactory;
	}

	/**
	 * Forces a refresh on the PermissionsInterface instance.
	 */
	private function refreshPermissionsInstance()
	{
		$this->permissionsInstance = $this->makePermissionsInstance();
	}
}
