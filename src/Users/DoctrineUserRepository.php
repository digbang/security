<?php namespace Digbang\Security\Users;

use Cartalyst\Sentinel\Users\UserInterface;
use Digbang\Security\Permissions\Permissible;
use Digbang\Security\Persistences\PersistenceRepository;
use Digbang\Security\Roles\Role;
use Digbang\Security\Roles\Roleable;
use Digbang\Security\Roles\RoleRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use InvalidArgumentException;

abstract class DoctrineUserRepository extends EntityRepository implements UserRepository
{
	/**
	 * @var PersistenceRepository
	 */
	protected $persistences;

	/**
	 * @var RoleRepository
	 */
	protected $roles;

	/**
	 * @param EntityManager         $entityManager
	 * @param PersistenceRepository $persistences
	 * @param RoleRepository        $roles
	 */
    public function __construct(
	    EntityManager         $entityManager,
	    PersistenceRepository $persistences,
		RoleRepository        $roles
    )
    {
        parent::__construct($entityManager, $entityManager->getClassMetadata(
            $this->entityName()
        ));

	    $this->persistences = $persistences;
	    $this->roles        = $roles;
    }

	/**
	 * Get the User class name.
	 * @return string
	 */
    abstract protected function entityName();

	/**
	 * Create a new user based on the given credentials.
	 *
	 * @param array $credentials
	 *
	 * @return User
	 */
	abstract protected function createUser(array $credentials);

	/**
	 * Finds a user by the given primary key.
	 *
	 * @param  int $id
	 *
	 * @return User|null
	 */
	public function findById($id)
	{
		return $this->find($id);
	}

	/**
	 * Finds a user by the given credentials.
	 *
	 * @param  array $credentials
	 *
	 * @return UserInterface|null
	 */
	public function findByCredentials(array $credentials)
	{
		$queryBuilder = $this->createQueryBuilder('u');

		$this->createCredentialsCriteria($queryBuilder, $credentials);

		$queryBuilder->setMaxResults(1);

		return $queryBuilder->getQuery()->getOneOrNullResult();
	}

	/**
	 * Finds a user by the given persistence code.
	 *
	 * @param  string $code
	 *
	 * @return UserInterface|null
	 */
	public function findByPersistenceCode($code)
	{
        return $this->persistences->findUserByPersistenceCode($code);
	}

	/**
	 * Records a login for the given user.
	 *
	 * @param User $user
	 *
	 * @return UserInterface|bool
	 */
	public function recordLogin(UserInterface $user)
	{
		$user->recordLogin();

		return $this->save($user);
	}

	/**
	 * Records a logout for the given user.
	 *
	 * @param  UserInterface $user
	 *
	 * @return UserInterface|bool
	 */
	public function recordLogout(UserInterface $user)
	{
		return $this->save($user);
	}

	/**
	 * Validate the password of the given user.
	 *
	 * @param User $user
	 * @param array $credentials
	 *
	 * @return bool
	 */
	public function validateCredentials(UserInterface $user, array $credentials)
	{
		return $user->checkPassword(array_get($credentials, 'password'));
	}

	/**
	 * Validate if the given user is valid for creation.
	 *
	 * @param  array $credentials
	 * @return bool
	 * @throws \InvalidArgumentException
	 */
	public function validForCreation(array $credentials)
	{
		return $this->validate($credentials);
	}

	/**
	 * Validate if the given user is valid for updating.
	 *
	 * @param  UserInterface|int $user
	 * @param  array             $credentials
	 * @return bool
	 * @throws \InvalidArgumentException
	 */
	public function validForUpdate($user, array $credentials)
	{
		if ($user instanceof UserInterface)
		{
			$user = $user->getUserId();
		}

		return $this->validate($credentials, $user);
	}

	/**
	 * @param array $credentials
	 * @param int   $id
	 *
	 * @return bool
	 * @throws InvalidArgumentException
	 */
	protected function validate(array $credentials, $id = null)
	{
		if ($id !== null)
		{
			return true;
		}

		if (count(array_only($credentials, ['password', 'email', 'username'])) < 3)
		{
			return false;
		}

		return true;
	}

	/**
	 * Creates a user.
	 *
	 * @param array    $credentials
	 * @param \Closure $callback
	 *
	 * @return User
	 */
	public function create(array $credentials, \Closure $callback = null)
	{
		$user = $this->createUser($credentials);

        if ($callback)
        {
            if ($callback($user) === false)
            {
                return false;
            }
        }

        return $this->save($user);
	}

	/**
	 * Updates a user.
	 *
	 * @param  User|int $user
	 * @param  array    $credentials
	 *
	 * @return User
	 */
	public function update($user, array $credentials)
	{
		if (! $user instanceof User)
		{
            $user = $this->findById($user);
        }

		if ($user instanceof Roleable && isset($credentials['roles']))
		{
			foreach ($user->getRoles() as $role)
			{
				/** @var Role $role */
				$idx = array_search($role->getRoleSlug(), $credentials['roles']);
				if ($idx === false)
				{
					$user->removeRole($role);
				}
				else
				{
					unset($credentials['roles'][$idx]);
				}
			}

			foreach ($credentials['roles'] as $roleSlug)
			{
				/** @var Role|null $role */
				$role = $this->roles->findBySlug($roleSlug);

				if (! $role)
				{
					throw new \InvalidArgumentException("Role [$roleSlug] does not exist.");
				}

				$user->addRole($role);
			}

			unset($credentials['roles']);
		}

		$user->update($credentials);

        return $this->save($user);
	}

	/**
	 * {@inheritdoc}
	 */
	public function destroy(User $user)
	{
		$this->_em->remove($user);
		$this->_em->flush();
	}

	/**
	 * @param UserInterface $user
	 *
	 * @return bool|UserInterface
	 */
	protected function save(UserInterface $user)
	{
		try
		{
			$entityManager = $this->getEntityManager();
			$entityManager->persist($user);
			$entityManager->flush();

			return $user;
		}
		catch (\Exception $e)
		{
			return false;
		}
	}

	protected function createCredentialsCriteria(QueryBuilder $queryBuilder, array $credentials)
	{
		$expr = $queryBuilder->expr();
		$alias = $queryBuilder->getRootAliases()[0];

		if (array_key_exists('login', $credentials))
		{
			$queryBuilder->andWhere($expr->orX(
				$expr->eq($expr->lower($alias . '.email.address'), $expr->lower(':login')),
				$expr->eq($expr->lower($alias . '.username'), $expr->lower(':login'))
			));

			$queryBuilder->setParameter('login', $credentials['login']);
		}
		else
		{
			if (empty(array_only($credentials, ['email', 'username'])))
			{
				throw new \InvalidArgumentException("Invalid credentials given. Credentials must have either a 'login' or 'email' / 'username' keys.");
			}

			if (isset($credentials['email']))
			{
				$queryBuilder->andWhere($expr->eq($expr->lower($alias . '.email.address'), $expr->lower(':email')));
				$queryBuilder->setParameter('email', $credentials['email']);
			}

			if (isset($credentials['username']))
			{
				$queryBuilder->andWhere($expr->eq($expr->lower($alias . '.username'), $expr->lower(':username')));
				$queryBuilder->setParameter('username', $credentials['username']);
			}

		}

		return $queryBuilder;
	}
}
