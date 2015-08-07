<?php namespace Digbang\Security\Users;

use Cartalyst\Sentinel\Persistences\PersistenceRepositoryInterface;
use Cartalyst\Sentinel\Users\UserInterface;
use Cartalyst\Sentinel\Users\UserRepositoryInterface;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use InvalidArgumentException;

abstract class DoctrineUserRepository extends EntityRepository implements UserRepositoryInterface
{
	/**
	 * @type PersistenceRepositoryInterface
	 */
	protected $persistenceRepository;

	/**
	 * @type \Closure|null
	 */
	protected $permissionsFactory;

	/**
	 * @param EntityManager                  $entityManager
	 * @param PersistenceRepositoryInterface $persistenceRepository
	 * @param \Closure                        $permissionsFactory
	 */
    public function __construct(EntityManager $entityManager, PersistenceRepositoryInterface $persistenceRepository, \Closure $permissionsFactory = null)
    {
        parent::__construct($entityManager, $entityManager->getClassMetadata(
            $this->entityName()
        ));

	    $this->persistenceRepository = $persistenceRepository;
	    $this->permissionsFactory    = $permissionsFactory;
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

		$this->addCredentialsCriteria($credentials, $queryBuilder);

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
        return $this->persistenceRepository->findUserByPersistenceCode($code);
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
            $result = $callback($user);

            if ($result === false)
            {
                return false;
            }
        }

        $this->save($user);

        return $user;
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

        $user->update($credentials);

        $this->save($user);

		return $user;
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

	protected function addCredentialsCriteria(array $credentials, QueryBuilder $queryBuilder)
	{
		$criteria = Criteria::create();
		$parameters = [];

		if (array_key_exists('login', $credentials))
		{
			$criteria->andWhere($criteria->expr()->orX(
				$criteria->expr()->eq('email.address', ':login'),
				$criteria->expr()->eq('username', ':login')
			));

			$parameters['login'] = $credentials['login'];
		}
		else
		{
			if (empty(array_only($credentials, ['email', 'username'])))
			{
				throw new \InvalidArgumentException("Invalid credentials given. Credentials must have either a 'login' or 'email' / 'username' keys.");
			}

			if (isset($credentials['email']))
			{
				$criteria->andWhere($criteria->expr()->eq('email.address', ':email'));

				$parameters['email'] = $credentials['email'];
			}

			if (isset($credentials['username']))
			{
				$criteria->andWhere($criteria->expr()->eq('username', ':username'));

				$parameters['username'] = $credentials['username'];
			}
		}

		$queryBuilder->addCriteria($criteria);
		$queryBuilder->setParameters($parameters);
	}
}
