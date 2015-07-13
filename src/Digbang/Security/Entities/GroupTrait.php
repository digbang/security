<?php namespace Digbang\Security\Entities;

use Digbang\Doctrine\TimestampsTrait;
use Digbang\Security\Contracts\Permission as PermissionInterface;
use Doctrine\Common\Collections\ArrayCollection;

trait GroupTrait
{
	use TimestampsTrait;

	/**
	 * @type int
	 */
	private $id;

	/**
	 * @type string
	 */
	private $name;

	/**
	 * @type ArrayCollection
	 */
	private $permissions;

	/**
	 * @type \Digbang\Security\Repositories\DoctrineGroupRepository
	 */
	private $groupRepository;

	/**
	 * @type ArrayCollection
	 */
	private $users;

	/**
	 * Returns the group's ID.
	 *
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Returns the group's name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Returns permissions for the group.
	 *
	 * @return array
	 */
	public function getPermissions()
	{
		return $this->permissions->toArray();
	}

	/**
	 * Saves the group.
	 *
	 * @return bool
	 */
	public function save()
	{
		$this->groupRepository->save($this);

		return true;
	}

	/**
	 * Delete the group.
	 *
	 * @return bool
	 */
	public function delete()
	{
		$this->groupRepository->delete($this);

		return true;
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
	 * @param \Digbang\Security\Repositories\DoctrineGroupRepository $groupRepository
	 */
	public function setRepository(\Doctrine\Common\Persistence\ObjectRepository $groupRepository)
	{
		$this->groupRepository = $groupRepository;
	}

	/**
	 * @param string $name
	 */
	public function changeName($name)
	{
		$this->name = $name;
	}

	/**
	 * @param array $permissions
	 */
	public function setPermissions(array $permissions)
	{
		foreach ($this->permissions as $permission)
		{
			/** @type GroupPermission $permission */
			if (! in_array((string) $permission, $permissions))
			{
				$this->permissions->removeElement($permission);
			}
			else
			{
				unset($permissions[array_search((string) $permission, $permissions)]);
			}
		}

		foreach ($permissions as $newPermission)
		{
			$this->permissions->add($this->createGroupPermission($newPermission));
		}
	}

	/**
	 * Hard-coded GroupPermission creation.
	 * Override this method to use custom GroupPermission objects.
	 *
	 * @param string $permission
	 *
	 * @return GroupPermission
	 * @internal
	 */
	protected function createGroupPermission($permission)
	{
		return new GroupPermission($this, $permission);
	}

	/**
	 * @param string|array $permissions
	 * @param bool         $all
	 *
	 * @return bool
	 */
	public function hasAccess($permissions, $all = true)
	{
		return array_reduce((array) $permissions, function($carry, $permission) use ($all) {
			if ($all) {
				return $carry && $this->hasSinglePermission($permission);
			}

			return $carry || $this->hasSinglePermission($permission);
		}, $all);
	}

	/**
	 * @param string $aPermission
	 *
	 * @return bool
	 * @internal
	 */
	protected function hasSinglePermission($aPermission)
	{
		return $this->permissions->exists(
			function($key, PermissionInterface $permission) use ($aPermission) {
				return $permission->allows($aPermission);
			}
		);
	}
}

