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
		if ($create || $this->permissions->containsKey($permission))
		{
			$this->permissions->set($permission, $this->createPermission($permission, $value));

			$this->refreshPermissionsInstance();
		}

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function removePermission($permission)
	{
		$this->permissions->remove($permission);

		$this->refreshPermissionsInstance();

		return $this;
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
