<?php namespace Digbang\Security\Repositories;

use Cartalyst\Sentry\Groups\GroupInterface;
use Cartalyst\Sentry\Hashing\HasherInterface;
use Cartalyst\Sentry\Users\WrongPasswordException;
use Cartalyst\Sentry\Users\UserNotFoundException;
use Cartalyst\Sentry\Users\ProviderInterface;
use Digbang\Security\Contracts\User;
use Digbang\Security\Contracts\RepositoryAware;
use Digbang\Security\Entities\User as DefaultUser;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Illuminate\Config\Repository;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use RuntimeException;

class DoctrineUserRepository extends EntityRepository implements ProviderInterface
{
    /**
     * @type HasherInterface
     */
    private $hasher;

	/**
	 * @type string
	 */
	private $entityName;

    public function __construct(EntityManagerInterface $em, Repository $config, HasherInterface $hasher)
    {
        parent::__construct($em, $em->getClassMetadata(
            $this->entityName = $config->get('security::auth.users.model', DefaultUser::class)
        ));

        $this->hasher = $hasher;
    }

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
        $user = $this->findOneBy(['email' => $login]);

        if (! $user)
        {
            throw new UserNotFoundException("User $login not found.");
        }

        return $this->user($user);
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
        $user = $this->findByLogin($credentials['email']);

        if (! $user)
        {
            throw new UserNotFoundException("User " . $credentials['email'] . " not found.");
        }

	    if (! $this->checkHash($credentials['password'], $user->getPassword()))
	    {
		    throw new WrongPasswordException(
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

        return $this->user($user);
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

    private function user(User $user)
    {
	    if ($user instanceof RepositoryAware)
	    {
		    $user->setRepository($this);
	    }

        return $user;
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
