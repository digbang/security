<?php namespace Digbang\Security\Reminders;

use Carbon\Carbon;
use Cartalyst\Sentinel\Reminders\ReminderRepositoryInterface;
use Cartalyst\Sentinel\Users\UserInterface;
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
	 * @param EntityManager $entityManager
	 * @param int           $expires
	 */
	public function __construct(EntityManager $entityManager, $expires = 259200)
	{
		parent::__construct($entityManager, $entityManager->getClassMetadata($this->entityName()));

		$this->expires = $expires;
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
	 * @param  \Cartalyst\Sentinel\Users\UserInterface $user
	 * @param  string                                  $code
	 * @param  string                                  $password
	 *
	 * @return bool
	 */
	public function complete(UserInterface $user, $code, $password)
	{
		/** @type DefaultReminder $reminder */
        $reminder = $this->findIncomplete($user, $code);

        if ($reminder === null)
        {
            return false;
        }

		$reminder->complete();

		$this->save($reminder);

        return true;
	}

	/**
	 * Remove expired reminder codes.
	 *
	 * @return int
	 */
	public function removeExpired()
	{
		$queryBuilder = $this->createQueryBuilder('r');

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
	 * @param DefaultReminder $reminder
	 */
	protected function save(DefaultReminder $reminder)
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
	 * @return DefaultReminder|null
	 *
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	protected function findIncomplete(UserInterface $user, $code = null)
	{
		$queryBuilder = $this->createQueryBuilder('r');

		$queryBuilder
			->where('r.' . get_class($user) . ' = :user')
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
