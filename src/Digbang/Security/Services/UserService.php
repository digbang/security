<?php namespace Digbang\Security\Services;

use Digbang\Security\Contracts\User;
use Digbang\Security\Repositories\DoctrineUserRepository;
use Doctrine\ORM\EntityManagerInterface;

class UserService
{
	/**
	 * @type DoctrineUserRepository
	 */
	private $userRepository;

	/**
	 * @type EntityManagerInterface
	 */
	private $entityManager;

	/**
	 * @type GroupService
	 */
	private $groupService;

	/**
	 * @param DoctrineUserRepository $userRepository
	 * @param EntityManagerInterface $entityManager
	 * @param GroupService           $groupService
	 */
	public function __construct(DoctrineUserRepository $userRepository, EntityManagerInterface $entityManager, GroupService $groupService)
	{
		$this->userRepository = $userRepository;
		$this->entityManager = $entityManager;
		$this->groupService = $groupService;
	}

	/**
	 * @param string $email
	 * @param string $password
	 * @param string $firstName
	 * @param string $lastName
	 * @param bool   $activated
	 * @param array  $groups
	 * @param array  $permissions
	 * @param bool   $superUser
	 *
	 * @return User
	 * @throws \Exception
	 */
	public function create($email, $password, $firstName = null, $lastName = null, $activated = false, array $groups = [], array $permissions = [], $superUser = false)
	{
		$this->entityManager->beginTransaction();

		try
		{
			/** @type \Digbang\Security\Contracts\User $user */
			$user = $this->userRepository->create(compact('email', 'password'));

			$user->named($firstName, $lastName);

			if ($activated)
			{
				$user->forceActivation();
			}

			if ($superUser)
			{
				$user->promoteToSuperUser();
			}

			$user->setAllGroups($this->groupService->findAll($groups));
			$user->setAllPermissions($permissions);

			$user->save();

			$this->entityManager->commit();

			return $user;
		}
		catch (\Exception $e)
		{
			$this->entityManager->rollback();

			throw $e;
		}
	}

	/**
	 * @param $id
	 *
	 * @return \Digbang\Security\Contracts\User
	 */
	public function find($id)
	{
		return $this->userRepository->findById($id);
	}

	/**
	 * @param User         $user
	 * @param string       $firstName
	 * @param string       $lastName
	 * @param string       $email
	 * @param string|null  $password
	 * @param bool|null    $activated
	 * @param array        $groups
	 * @param array        $permissions
	 *
	 * @return User
	 */
	public function edit(User $user, $firstName, $lastName, $email, $password = null, $activated = null, $groups = [], $permissions = [])
	{
		$user->named($firstName, $lastName);

		$user->changeEmail($email);

		if ($password !== null)
		{
			$user->changePassword($this->userRepository->hash($password));
		}

		$user->setAllGroups($this->groupService->findAll($groups));
		$user->setAllPermissions($permissions);

		if ($activated && ! $user->isActivated())
		{
			$user->forceActivation();
		}
		elseif ($activated === false && $user->isActivated())
		{
			$user->deactivate();
		}

		$user->save();

		return $user;
	}

	/**
	 * @param int $id
	 */
	public function delete($id)
	{
		$user = $this->userRepository->findById($id);

		$user->delete();
	}

	/**
	 * @param string|null   $email
	 * @param string|null   $firstName
	 * @param string|null   $lastName
	 * @param bool|null     $activated
	 * @param string|null   $orderBy
	 * @param string        $orderSense
	 * @param int           $limit
	 * @param int           $offset
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function search($email = null, $firstName = null, $lastName = null, $activated = null, $orderBy = null, $orderSense = 'asc', $limit = 10, $offset = 0)
	{
		return $this->userRepository->search($email, $firstName, $lastName, $activated, $orderBy, $orderSense, $limit, $offset);
	}

	/**
	 * @param string $login
	 *
	 * @return User
	 */
	public function findByLogin($login)
	{
		return $this->userRepository->findByLogin($login);
	}
}
