<?php namespace Digbang\Security\Users;

use Cartalyst\Sentinel\Hashing\HasherInterface;
use Cartalyst\Sentinel\Persistences\PersistenceRepositoryInterface;
use Cartalyst\Sentinel\Users\UserInterface;
use Cartalyst\Sentinel\Users\UserRepositoryInterface;
use Closure;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use InvalidArgumentException;

abstract class DoctrineUserRepository extends EntityRepository implements UserRepositoryInterface
{
    /**
     * @type HasherInterface
     */
    private $hasher;

	/**
	 * @type PersistenceRepositoryInterface
	 */
	private $persistenceRepository;

	/**
	 * @param EntityManager                  $entityManager
	 * @param HasherInterface                $hasher
	 * @param PersistenceRepositoryInterface $persistenceRepository
	 */
    public function __construct(EntityManager $entityManager, HasherInterface $hasher, PersistenceRepositoryInterface $persistenceRepository)
    {
        parent::__construct($entityManager, $entityManager->getClassMetadata(
            $this->entityName()
        ));

        $this->hasher = $hasher;
	    $this->persistenceRepository = $persistenceRepository;
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
*@return DefaultUser
	 */
	abstract protected function createUser(array $credentials);

	/**
	 * Finds a user by the given primary key.
	 *
	 * @param  int $id
	 *
*@return DefaultUser|null
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

		$queryBuilder->addCriteria(
			$this->createCredentialsCriteria($credentials)
		);

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
	 * @param  DefaultUser $user
	 *
*@return UserInterface|bool
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
	 * @param  DefaultUser  $user
	 * @param  array $credentials
	 *
	 * @return bool
	 */
	public function validateCredentials(UserInterface $user, array $credentials)
	{
		return $this->hasher->check($credentials['password'], $user->getPassword());
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

	protected function validate(array $credentials, $id = null)
	{
		if ($id !== null)
		{
			return true;
		}

		if (! array_key_exists('password', $credentials))
		{
			throw new InvalidArgumentException('You have not passed a [password].');
		}

		if (! array_key_exists('email', $credentials))
		{
			throw new InvalidArgumentException('You have not passed an [email].');
		}

		if (! array_key_exists('username', $credentials))
		{
			throw new InvalidArgumentException('You have not passed an [username].');
		}

		return true;
	}

	/**
	 * Creates a user.
	 *
	 * @param array    $credentials
	 * @param \Closure $callback
	 *
	 * @return DefaultUser
	 */
	public function create(array $credentials, Closure $callback = null)
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
	 * @param  DefaultUser|int $user
	 * @param  array    $credentials
	 *
	 * @return DefaultUser
	 */
	public function update($user, array $credentials)
	{
		if (! $user instanceof DefaultUser)
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

	protected function createCredentialsCriteria(array $credentials)
	{
		$criteria = Criteria::create();

		if (array_key_exists('login', $credentials))
		{
			$criteria->andWhere($criteria->expr()->orX(
				$criteria->expr()->eq('email', $credentials['login']),
				$criteria->expr()->eq('username', $credentials['login'])
			));
		}
		else
		{
			foreach (array_only($credentials, ['email', 'username']) as $field => $value)
			{
				$criteria->andWhere($criteria->expr()->eq($field, $value));
			}
		}

		return $criteria;
	}
}
