<?php namespace Digbang\Security\Activations;

use Carbon\Carbon;
use Cartalyst\Sentinel\Activations\ActivationRepositoryInterface;
use Cartalyst\Sentinel\Users\UserInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping;
use Doctrine\ORM\NoResultException;

abstract class DoctrineActivationRepository extends EntityRepository implements ActivationRepositoryInterface
{
	/**
	 * @type int
	 */
	protected $expires;

	/**
	 * @param EntityManager $entityManager
	 * @param int           $expires
	 */
	public function __construct(EntityManager $entityManager, $expires = 259200)
	{
		parent::__construct($entityManager, $entityManager->getClassMetadata($this->entityName()));
		$this->expires = $expires;
	}

	/**
	 * Get the Activation class name.
	 * @return string
	 */
	abstract protected function entityName();

	/**
	 * Checks if a valid activation for the given user exists.
	 *
	 * @param  \Cartalyst\Sentinel\Users\UserInterface $user
	 * @param  string                                  $code
	 *
	 * @return bool
	 */
	public function exists(UserInterface $user, $code = null)
	{
		return $this->findIncomplete($user, $code) !== null;
	}

	/**
	 * Completes the activation for the given user.
	 *
	 * @param  \Cartalyst\Sentinel\Users\UserInterface $user
	 * @param  string                                  $code
	 *
	 * @return bool
	 */
	public function complete(UserInterface $user, $code)
	{
		/** @type Activation $activation */
        $activation = $this->findIncomplete($user, $code);

        if ($activation === null)
        {
            return false;
        }

		$activation->complete();

		$this->save($activation);

        return true;
	}

	/**
	 * Checks if a valid activation has been completed.
	 *
	 * @param  \Cartalyst\Sentinel\Users\UserInterface $user
	 *
	 * @return bool|Activation
	 */
	public function completed(UserInterface $user)
	{
		$queryBuilder = $this->createQueryBuilder('a');

		$queryBuilder
			->where('a.' . get_class($user) . ' = :user')
			->andWhere('completed = :completed');

		$queryBuilder
			->setParameters([
				'user' => $user,
				'completed' => true
			]);

		try
		{
			return $queryBuilder->getQuery()->getSingleResult();
		}
		catch (NoResultException $e)
		{
			return false;
		}
	}

	/**
	 * Remove an existing activation (deactivate).
	 *
	 * @param  \Cartalyst\Sentinel\Users\UserInterface $user
	 *
	 * @return bool|null
	 */
	public function remove(UserInterface $user)
	{
		$activation = $this->completed($user);

        if ($activation === false) {
            return false;
        }

		$entityManager = $this->getEntityManager();

		$entityManager->remove($activation);
		$entityManager->flush();

		return true;
	}

	/**
	 * Remove expired activation codes.
	 *
	 * @return int
	 */
	public function removeExpired()
	{
		$queryBuilder = $this->createQueryBuilder('a');

		$queryBuilder
			->delete()
			->where('completed = :completed')
			->andWhere('createdAt < :expires');

		$queryBuilder->setParameters([
			'completed' => false,
			'expires' => $this->expires()
		]);

		try
		{
			return $queryBuilder->getQuery()->getSingleScalarResult();
		}
		catch (NoResultException $e)
		{
			return 0;
		}
	}

	/**
	 * @return Carbon
	 */
	protected function expires()
	{
		return Carbon::now()->subSeconds($this->expires);
	}

	/**
	 * @param UserInterface $user
	 * @param string|null   $code
	 *
	 * @return Activation|null
	 *
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	protected function findIncomplete(UserInterface $user, $code = null)
	{
		$queryBuilder = $this->createQueryBuilder('a');

		$queryBuilder
			->where('a.' . get_class($user) . ' = :user')
			->andWhere('completed = :completed')
			->andWhere('createdAt > :expires');

		$queryBuilder
			->setParameter('user', $user)
			->setParameter('completed', false)
			->setParameter('expires', $this->expires());

        if ($code)
        {
            $queryBuilder
	            ->andWhere('code = :code')
	            ->setParameter('code', $code);
        }

		try
		{
            return $queryBuilder->getQuery()->getSingleResult();
		}
		catch (NoResultException $e)
		{
			return null;
		}
	}

	/**
	 * @param Activation $activation
	 */
	protected function save(Activation $activation)
	{
		$entityManager = $this->getEntityManager();

		$entityManager->persist($activation);
		$entityManager->flush();
	}
}
