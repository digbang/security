<?php namespace Digbang\Security\Reminders;

use Carbon\Carbon;
use Cartalyst\Sentinel\Reminders\ReminderRepositoryInterface;
use Cartalyst\Sentinel\Users\UserInterface;
use Cartalyst\Sentinel\Users\UserRepositoryInterface;
use Digbang\Security\Users\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping;
use Doctrine\ORM\NoResultException;

abstract class DoctrineReminderRepository extends EntityRepository implements ReminderRepositoryInterface
{
	/**
	 * @type int
	 */
	private $expires;

	/**
	 * @type UserRepositoryInterface
	 */
	private $userRepository;

	/**
	 * @param EntityManager           $entityManager
	 * @param UserRepositoryInterface $userRepository
	 * @param int                     $expires
	 */
	public function __construct(EntityManager $entityManager, UserRepositoryInterface $userRepository, $expires = 259200)
	{
		parent::__construct($entityManager, $entityManager->getClassMetadata($this->entityName()));

		$this->expires        = $expires;
		$this->userRepository = $userRepository;
	}

	/**
	 * Get the Reminder class name.
	 * @return string
	 */
	abstract protected function entityName();

	/**
	 * Check if a valid reminder exists.
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
	 * Complete reminder for the given user.
	 *
	 * @param  User   $user
	 * @param  string $code
	 * @param  string $password
	 *
	 * @return bool
	 */
	public function complete(UserInterface $user, $code, $password)
	{
        $reminder = $this->findIncomplete($user, $code);

        if ($reminder === null)
        {
            return false;
        }

		$credentials = ['password' => $password];

		if (! $this->userRepository->validForUpdate($user, $credentials))
		{
			return false;
		}

		$entityManager = $this->getEntityManager();
		$entityManager->beginTransaction();

		try
		{
			$this->userRepository->update($user, $credentials);

			$reminder->complete();
			$this->save($reminder);

			$entityManager->commit();
	        return true;
		}
		catch (\Exception $e)
		{
			$entityManager->rollback();

			return false;
		}
	}

	/**
	 * Remove expired reminder codes.
	 *
	 * @return int
	 */
	public function removeExpired()
	{
		$queryBuilder = $this->getEntityManager()->createQueryBuilder();

		$queryBuilder
			->delete()
			->from($this->entityName(), 'r')
			->where('r.completed = :completed')
			->andWhere('r.createdAt < :expires');

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
	 * @param Reminder $reminder
	 */
	protected function save(Reminder $reminder)
	{
		$entityManager = $this->getEntityManager();

		$entityManager->persist($reminder);
		$entityManager->flush();
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
	 * @return Reminder|null
	 *
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	protected function findIncomplete(UserInterface $user, $code = null)
	{
		$queryBuilder = $this->createQueryBuilder('r');

		$queryBuilder
			->where('r.user = :user')
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
}
