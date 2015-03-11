<?php namespace Digbang\Security\Services;

use Digbang\Security\Contracts\Group;
use Digbang\Security\Repositories\DoctrineGroupRepository;
use Illuminate\Support\Collection;

/**
 * Class GroupService
 *
 * @package Digbang\Security\Services
 */
class GroupService
{
	/**
	 * @type DoctrineGroupRepository
	 */
	private $groupRepository;

	/**
	 * @param DoctrineGroupRepository $groupRepository
	 */
	public function __construct(DoctrineGroupRepository $groupRepository)
	{
		$this->groupRepository = $groupRepository;
	}

	/**
	 * @return Collection
	 */
	public function all()
	{
		return new Collection($this->groupRepository->findAll());
	}

	/**
	 * @param int $id
	 *
	 * @return \Digbang\Security\Contracts\Group
	 * @throws \Cartalyst\Sentry\Groups\GroupNotFoundException
	 */
	public function find($id)
	{
		return $this->groupRepository->findById($id);
	}

	/**
	 * @param array $ids
	 *
	 * @return Collection
	 * @throws \Cartalyst\Sentry\Groups\GroupNotFoundException
	 */
	public function findAll(array $ids)
	{
		return new Collection(array_map([$this, 'find'], $ids));
	}

	/**
	 * @param string $name
	 * @param array  $permissions
	 *
	 * @return \Digbang\Security\Contracts\Group
	 */
	public function create($name, array $permissions = [])
	{
		return $this->groupRepository->create([
			'name'        => $name,
			'permissions' => $permissions
		]);
	}

	/**
	 * @param Group  $group
	 * @param string $name
	 * @param array  $permissions
	 *
	 * @return void
	 */
	public function edit(Group $group, $name, array $permissions)
	{
		$group->changeName($name);
		$group->setPermissions($permissions);

		$this->groupRepository->save($group);
	}

	/**
	 * @param int $id
	 *
	 * @return void
	 */
	public function delete($id)
	{
		$group = $this->groupRepository->find($id);

		$this->groupRepository->delete($group);
	}

	/**
	 * @param string|null   $name
	 * @param string|null   $permission
	 * @param string|null   $orderBy
	 * @param string        $orderSense
	 * @param int           $limit
	 * @param int           $offset
	 *
	 * @return Collection
	 */
	public function search($name = null, $permission = null, $orderBy = null, $orderSense = 'asc', $limit = 10, $offset = 0)
	{
		return new Collection(
			$this->groupRepository->search($name, $permission, $orderBy, $orderSense, $limit, $offset)
		);
	}
}
