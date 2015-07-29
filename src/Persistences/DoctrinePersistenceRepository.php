<?php namespace Digbang\Security\Persistences;

use Cartalyst\Sentinel\Cookies\CookieInterface;
use Cartalyst\Sentinel\Persistences\PersistableInterface;
use Cartalyst\Sentinel\Persistences\PersistenceInterface;
use Cartalyst\Sentinel\Persistences\PersistenceRepositoryInterface;
use Cartalyst\Sentinel\Sessions\SessionInterface;
use Digbang\Security\Contracts\Entities\Persistence;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping;

abstract class DoctrinePersistenceRepository extends EntityRepository implements PersistenceRepositoryInterface
{
	/**
	 * @type SessionInterface
	 */
	private $session;

	/**
	 * @type CookieInterface
	 */
	private $cookie;

	/**
	 * @type bool
	 */
	private $single = false;

	/**
	 * @param EntityManager    $entityManager The EntityManager to use.
	 * @param SessionInterface $session
	 * @param CookieInterface  $cookie
	 * @param bool             $single
	 */
	public function __construct(EntityManager $entityManager, SessionInterface $session, CookieInterface $cookie, $single = false)
	{
		parent::__construct($entityManager, $entityManager->getClassMetadata(
			$this->entityName()
		));

		$this->session = $session;
		$this->cookie = $cookie;
		$this->single = $single;
	}

	abstract protected function entityName();

	abstract protected function create(PersistableInterface $persistable, $code);

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
	 * @param PersistableInterface $persistable
	 * @param bool|false           $remember
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

		$queryBuilder->getQuery()->execute();
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
			->where('p.' . get_class($persistable) . ' = :persistable')
			->andWhere('p.code != :code');

		$queryBuilder->setParameters([
			'persistable' => $persistable,
			'code'        => $code
		]);

		$queryBuilder->getQuery()->execute();
	}
}
