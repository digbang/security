<?php

namespace Digbang\Security\Reminders;

use Carbon\Carbon;
use Cartalyst\Sentinel\Users\UserInterface;
use Digbang\Security\Users\User;
use Digbang\Security\Users\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

abstract class DoctrineReminderRepository extends EntityRepository implements ReminderRepository
{
    /**
     * @var int
     */
    private $expires;

    /**
     * @var UserRepository
     */
    private $users;

    /**
     * @param  EntityManager  $entityManager
     * @param  UserRepository  $users
     */
    public function __construct(EntityManager $entityManager, UserRepository $users)
    {
        parent::__construct($entityManager, $entityManager->getClassMetadata($this->entityName()));

        $this->users = $users;
    }

    /**
     * Check if a valid reminder exists.
     *
     * @param  User  $user
     * @param  string  $code
     * @return bool
     */
    public function exists(UserInterface $user, string $code = null): bool
    {
        return $this->findIncomplete($user, $code) !== null;
    }

    /**
     * Complete reminder for the given user.
     *
     * @param  User  $user
     * @param  string  $code
     * @param  string  $password
     * @return bool
     */
    public function complete(UserInterface $user, string $code, string $password): bool
    {
        $reminder = $this->findIncomplete($user, $code);

        if ($reminder === null) {
            return false;
        }

        $credentials = ['password' => $password];

        if (! $this->users->validForUpdate($user, $credentials)) {
            return false;
        }

        $entityManager = $this->getEntityManager();
        $entityManager->beginTransaction();

        try {
            $this->users->update($user, $credentials);

            $reminder->complete();
            $this->save($reminder);

            $entityManager->commit();

            return true;
        } catch (\Exception $e) {
            $entityManager->rollback();

            return false;
        }
    }

    /**
     * Remove expired reminder codes.
     *
     * @return bool
     */
    public function removeExpired(): bool
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder
            ->delete()
            ->from($this->entityName(), 'r')
            ->where('r.completed = :completed')
            ->andWhere('r.createdAt < :expires');

        $queryBuilder->setParameters([
            'completed' => false,
            'expires' => $this->expires(),
        ]);

        try {
            return (bool) $queryBuilder->getQuery()->getSingleScalarResult();
        } catch (NoResultException $e) {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function setExpires($expires)
    {
        $this->expires = $expires;
    }

    /**
     * Get the Reminder class name.
     *
     * @return string
     */
    abstract protected function entityName();

    /**
     * @param  Reminder  $reminder
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
     * @param  UserInterface  $user
     * @param  string|null  $code
     * @return Reminder|null
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function findIncomplete(UserInterface $user, $code = null)
    {
        $queryBuilder = $this->createQueryBuilder('r');

        $queryBuilder
            ->where('r.user = :user')
            ->andWhere('r.completed = :completed')
            ->andWhere('r.createdAt > :expires');

        $queryBuilder
            ->setParameter('user', $user)
            ->setParameter('completed', false)
            ->setParameter('expires', $this->expires());

        if ($code) {
            $queryBuilder
                ->andWhere('r.code = :code')
                ->setParameter('code', $code);
        }

        try {
            return $queryBuilder->getQuery()->getSingleResult();
        } catch (NoResultException $e) {
            return null;
        }
    }
}
