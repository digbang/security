<?php namespace Digbang\Security\Persistences;

use Cartalyst\Sentinel\Cookies\CookieInterface;
use Cartalyst\Sentinel\Persistences\PersistableInterface;
use Cartalyst\Sentinel\Sessions\SessionInterface;
use Digbang\Security\Users\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping;

abstract class DoctrinePersistenceRepository extends EntityRepository implements PersistenceRepository
{
	/**
	 * @var SessionInterface
	 */
	private $session;

	/**
	 * @var CookieInterface
	 */
	private $cookie;

	/**
	 * @var bool
	 */
	private $single = false;

	/**
	 * @param EntityManager    $entityManager The EntityManager to use.
	 * @param SessionInterface $session
	 * @param CookieInterface  $cookie
	 */
	public function __construct(EntityManager $entityManager, SessionInterface $session, CookieInterface $cookie)
	{
		parent::__construct($entityManager, $entityManager->getClassMetadata(
			$this->entityName()
		));

		$this->session = $session;
		$this->cookie = $cookie;
	}

	/**
	 * Get the Persistence class name.
	 * @return string
	 */
	abstract protected function entityName();

	/**
     * Create a new persistence.
     *
     * @param  User $user
	 * @param string $code
     * @return Persistence
     */
	abstract protected function create(User $user, $code);

	/**
	 * Checks for a persistence code in the current session.
	 *
	 * @return string
	 */
	public function check()
	{
		if ($code = $this->session->get())
		{
            return $code;
        }

        if ($code = $this->cookie->get())
        {
            return $code;
        }
	}

	/**
	 * Finds a persistence by persistence code.
	 *
	 * @param  string $code
	 *
	 * @return Persistence|false
	 */
	public function findByPersistenceCode($code)
	{
		return $this->findOneBy(['code' => $code]) ?: false;
	}

	/**
	 * Finds a user by persistence code.
	 *
	 * @param  string $code
	 *
	 * @return \Digbang\Security\Users\DefaultUser|false
	 */
	public function findUserByPersistenceCode($code)
	{
		$persistence = $this->findByPersistenceCode($code);

		if ($persistence)
		{
			return $persistence->getUser();
		}

		return false;
	}

	/**
	 * Adds a new user persistence to the current session and attaches the user.
	 *
	 * @param User $persistable
	 * @param bool $remember
	 * @return bool
	 */
	public function persist(PersistableInterface $persistable, $remember = false)
	{
		try
		{
			if ($this->single)
			{
	            $this->flush($persistable);
	        }

	        $code = $persistable->generatePersistenceCode();

	        $this->session->put($code);

	        if ($remember === true) {
	            $this->cookie->put($code);
	        }

	        $persistence = $this->create($persistable, $code);

			$entityManager = $this->getEntityManager();
			$entityManager->persist($persistence);
			$entityManager->flush();

			return true;
		}
		catch (\Exception $e)
		{
			return false;
		}
	}

	/**
	 * Adds a new user persistence, to remember.
	 *
	 * @param  PersistableInterface $persistable
	 *
	 * @return bool
	 */
	public function persistAndRemember(PersistableInterface $persistable)
	{
		return $this->persist($persistable, true);
	}

	/**
	 * Removes the persistence bound to the current session.
	 * @return bool|null
	 */
	public function forget()
	{
		$code = $this->check();

        if ($code === null)
        {
            return null;
        }

        $this->session->forget();
        $this->cookie->forget();

        return $this->remove($code);
	}

	/**
	 * Removes the given persistence code.
	 *
	 * @param  string $code
	 *
	 * @return bool|null
	 */
	public function remove($code)
	{
		$entityManager = $this->getEntityManager();

		$queryBuilder = $entityManager->createQueryBuilder();
		$queryBuilder
			->delete($this->entityName(), 'p')
			->where('p.code = :code')
			->setParameter('code', $code);

		return $queryBuilder->getQuery()->execute();
	}

	/**
	 * Flushes persistences for the given user.
	 *
	 * @param  PersistableInterface $persistable
	 * @param  bool                 $forget
	 *
	 * @return void
	 */
	public function flush(PersistableInterface $persistable, $forget = true)
	{
		if ($forget)
		{
            $this->forget();
        }

		$code = $this->check();

		$entityManager = $this->getEntityManager();
		$queryBuilder = $entityManager->createQueryBuilder();

		$queryBuilder
			->delete($this->entityName(), 'p')
			->where('p.user = :persistable')
			->andWhere('p.code != :code');

		$queryBuilder->setParameters([
			'persistable' => $persistable,
			'code'        => $code
		]);

		$queryBuilder->getQuery()->execute();
	}

	/**
	 * {@inheritdoc}
	 */
	public function setPersistenceMode($mode = 'single')
	{
		$this->single = $mode === 'single';
	}
}
