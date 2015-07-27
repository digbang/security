<?php namespace Digbang\Security\Users;

use Cartalyst\Sentinel\Roles\RoleInterface;
use Cartalyst\Sentinel\Hashing\HasherInterface;
use Cartalyst\Sentinel\Users\UserRepositoryInterface;
use Closure;
use Digbang\Security\Contracts\User as UserInterface;
use Digbang\Security\Contracts\RepositoryAware;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Illuminate\Config\Repository;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use RuntimeException;

class DoctrineUserRepository extends EntityRepository implements UserRepositoryInterface
{
    /**
     * @type HasherInterface
     */
    private $hasher;

	/**
	 * @type string
	 */
	private $entityName;

	/**
	 * @param EntityManager   $entityManager
	 * @param Repository      $config
	 * @param HasherInterface $hasher
	 */
    public function __construct(EntityManager $entityManager, Repository $config, HasherInterface $hasher)
    {
        parent::__construct($entityManager, $entityManager->getClassMetadata(
            $this->entityName = $config->get('digbang.security.auth.users.model', User::class)
        ));

        $this->hasher = $hasher;
    }

	/**
	 * Finds a user by the given primary key.
	 *
	 * @param  int $id
	 * @return UserInterface
	 */
	public function findById($id)
	{
		/** @type User $user */
		$user = $this->find($id);

        if (! $user)
        {
            throw new \InvalidArgumentException("User $id not found.");
        }

        return $this->user($user);
	}

	/**
	 * Finds a user by the given credentials.
	 *
	 * @param  array $credentials
	 *
	 * @return \Cartalyst\Sentinel\Users\UserInterface
	 */
	public function findByCredentials(array $credentials)
	{
		$queryBuilder = $this->createQueryBuilder('u');

		$queryBuilder->addCriteria(
			$this->createCredentialsCriteria($queryBuilder, $credentials)
		);

		$queryBuilder->setMaxResults(1);

		$user = $queryBuilder->getQuery()->getOneOrNullResult();

        if (! $user)
        {
            throw new \InvalidArgumentException("User " . $credentials['email'] . " not found.");
        }

		return $this->user($user);
	}

	/**
	 * Finds a user by the given persistence code.
	 *
	 * @param  string $code
	 *
	 * @return \Cartalyst\Sentinel\Users\UserInterface
	 */
	public function findByPersistenceCode($code)
	{
		// TODO: Implement findByPersistenceCode() method.
	}

	/**
	 * Records a login for the given user.
	 *
	 * @param  \Cartalyst\Sentinel\Users\UserInterface $user
	 *
	 * @return \Cartalyst\Sentinel\Users\UserInterface|bool
	 */
	public function recordLogin(\Cartalyst\Sentinel\Users\UserInterface $user)
	{
		// TODO: Implement recordLogin() method.
	}

	/**
	 * Records a logout for the given user.
	 *
	 * @param  \Cartalyst\Sentinel\Users\UserInterface $user
	 *
	 * @return \Cartalyst\Sentinel\Users\UserInterface|bool
	 */
	public function recordLogout(\Cartalyst\Sentinel\Users\UserInterface $user)
	{
		// TODO: Implement recordLogout() method.
	}

	/**
	 * Validate the password of the given user.
	 *
	 * @param  \Cartalyst\Sentinel\Users\UserInterface $user
	 * @param  array                                   $credentials
	 *
	 * @return bool
	 */
	public function validateCredentials(\Cartalyst\Sentinel\Users\UserInterface $user, array $credentials)
	{
		if (! $this->checkHash($credentials['password'], $user->getPassword()))
	    {
		    throw new \InvalidArgumentException(
			    "A user was found, but passwords did not match."
		    );
	    }

	    if (
		    method_exists($this->hasher, 'needsRehashed') &&
		    $this->hasher->needsRehashed($user->getPassword())
	    )
	    {
		    // The algorithm used to create the hash is outdated and insecure.
		    // Rehash the password and save.
		    $user->changePassword($this->hasher->hash($credentials['password']));
		    $user->save();
	    }
	}

	/**
	 * Validate if the given user is valid for creation.
	 *
	 * @param  array $credentials
	 *
	 * @return bool
	 */
	public function validForCreation(array $credentials)
	{
		// TODO: Implement validForCreation() method.
	}

	/**
	 * Validate if the given user is valid for updating.
	 *
	 * @param  \Cartalyst\Sentinel\Users\UserInterface|int $user
	 * @param  array                                       $credentials
	 *
	 * @return bool
	 */
	public function validForUpdate($user, array $credentials)
	{
		// TODO: Implement validForUpdate() method.
	}

	/**
	 * Creates a user.
	 *
	 * @param  array    $credentials
	 * @param  \Closure $callback
	 *
	 * @return \Cartalyst\Sentinel\Users\UserInterface
	 */
	public function create(array $credentials, Closure $callback = null)
	{
		// TODO: Implement create() method.
	}

	/**
	 * Updates a user.
	 *
	 * @param  \Cartalyst\Sentinel\Users\UserInterface|int $user
	 * @param  array                                       $credentials
	 *
	 * @return \Cartalyst\Sentinel\Users\UserInterface
	 */
	public function update($user, array $credentials)
	{
		// TODO: Implement update() method.
	}

	/**
	 * @param User $user
	 * @return User
	 */
	private function user(User $user)
    {
	    if ($user instanceof RepositoryAware)
	    {
		    $user->setRepository($this);
	    }

        return $user;
    }

	private function createCredentialsCriteria(array $credentials)
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

class OldRepo
{
    /**
     * Finds a user by the given user ID.
     *
     * @param  mixed $id
     *
     * @return \Digbang\Security\Contracts\User
     * @throws \Cartalyst\Sentry\Users\UserNotFoundException
     */
    public function findById($id)
    {
        $user = $this->find($id);

        if (! $user)
        {
            throw new UserNotFoundException("User $id not found.");
        }

        return $this->user($user);
    }

    /**
     * Finds a user by the login value.
     *
     * @param  string $login
     *
     * @return \Digbang\Security\Contracts\User
     * @throws \Cartalyst\Sentry\Users\UserNotFoundException
     */
    public function findByLogin($login)
    {

    }

    /**
     * Finds a user by the given credentials.
     *
     * @param  array $credentials
     *
     * @return \Digbang\Security\Contracts\User
     * @throws \Cartalyst\Sentry\Users\UserNotFoundException
     */
    public function findByCredentials(array $credentials)
    {

    }

    /**
     * Finds a user by the given activation code.
     *
     * @param  string $code
     *
     * @return \Digbang\Security\Contracts\User
     * @throws \Cartalyst\Sentry\Users\UserNotFoundException
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function findByActivationCode($code)
    {
        $user = $this->findOneBy(['activationCode' => $code]);

        if (! $user)
        {
            throw new UserNotFoundException("User with code $code not found.");
        }

        return $this->user($user);
    }

    /**
     * Finds a user by the given reset password code.
     *
     * @param  string $code
     *
     * @return \Digbang\Security\Contracts\User
     * @throws RuntimeException
     * @throws \Cartalyst\Sentry\Users\UserNotFoundException
     */
    public function findByResetPasswordCode($code)
    {
        $user = $this->findOneBy(['resetPasswordCode' => $code]);

        if (! $user)
        {
            throw new UserNotFoundException("User with code $code not found.");
        }

        return $this->user($user);
    }

    /**
     * Returns all users who belong to
     * a group.
     *
     * @param  \Cartalyst\Sentry\Groups\GroupInterface $group
     *
     * @return Collection
     */
    public function findAllInGroup(GroupInterface $group)
    {
        return $this->userCollection(
            $this->findBy([
                'Group' => $group
            ])
        );
    }

    /**
     * Returns all users with access to
     * a permission(s).
     *
     * @param  string|array $permissions
     *
     * @return Collection
     */
    public function findAllWithAccess($permissions)
    {
        $criterias = [];

        foreach ((array) $permissions as $permission)
        {
            $criterias[] = Criteria::expr()->contains('Permissions', $permission);
        }

        return $this->userCollection(
            $this->findBy($criterias)
        );
    }

    /**
     * Returns all users with access to
     * any given permission(s).
     *
     * @param  array $permissions
     *
     * @return Collection
     */
    public function findAllWithAnyAccess(array $permissions)
    {
        $expressionBuilder = Criteria::expr();

        $permissions = (array) $permissions;

        $criterias = [];

        if (!empty($permissions))
        {
            $criterias[] = call_user_func_array([$expressionBuilder, 'orX'], array_map(function($permission){
                return Criteria::expr()->contains('Permissions', $permission);
            }, $permissions));
        }

        return $this->userCollection(
            $this->findBy($criterias)
        );
    }

    /**
     * Creates a user.
     *
     * @param  array $credentials
     *
     * @return \Digbang\Security\Contracts\User
     */
    public function create(array $credentials)
    {
	    $entityName = $this->entityName;

	    $user = $entityName::createFromCredentials(
		    $credentials['email'],
		    $this->hasher->hash($credentials['password'])
	    );

        $this->save($user);

        return $this->user($user);
    }

    /**
     * Returns an empty user object.
     *
     * @return \Digbang\Security\Contracts\User
     */
    public function getEmptyUser()
    {
        return $this->user(
            (new \ReflectionClass($this->entityName))->newInstanceWithoutConstructor()
        );
    }

    /**
     * @param User $user
     */
    public function save(User $user)
    {
        $em = $this->getEntityManager();

        $em->persist($user);
        $em->flush();
    }

    /**
     * @param User $user
     */
    public function delete(User $user)
    {
        $em = $this->getEntityManager();

        $em->remove($user);
        $em->flush();
    }

    /**
     * @param string $string
     * @param string $hashedString
     *
     * @return bool
     */
    public function checkHash($string, $hashedString)
    {
        return $this->hasher->checkhash($string, $hashedString);
    }

    private function userCollection(array $users)
    {
        return (new Collection($users))->map(function(User $user){
            return $this->user($user);
        });
    }

	/**
	 * @param string $string
	 *
	 * @return string
	 */
	public function hash($string)
	{
		return $this->hasher->hash($string);
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
		$filters = [];

		$expressionBuilder = Criteria::expr();

		if ($email !== null)
		{
			$filters[] = $expressionBuilder->contains('email', $email);
		}

		if ($firstName !== null)
		{
			$filters[] = $expressionBuilder->contains('firstName', $firstName);
		}

		if ($lastName !== null)
		{
			$filters[] = $expressionBuilder->contains('lastName', $lastName);
		}

		if ($activated !== null)
		{
			$filters[] = $expressionBuilder->eq('activated', (boolean) $activated);
		}

		$criteria = Criteria::create();

		if (!empty($filters))
		{
			$criteria->where($expressionBuilder->andX(...$filters));
		}

		if ($orderBy && $orderSense)
		{
			$criteria->orderBy([
				$orderBy => $orderSense
			]);
		}

		$criteria->setMaxResults($limit);
		$criteria->setFirstResult($offset);

		return $this->matching($criteria);
	}
}
