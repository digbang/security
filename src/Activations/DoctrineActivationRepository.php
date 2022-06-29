<?php

namespace Digbang\Security\Activations;

use Carbon\Carbon;
use Cartalyst\Sentinel\Users\UserInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

abstract class DoctrineActivationRepository extends EntityRepository implements ActivationRepository
{
    /**
     * @var int
     */
    protected $expires;

    /**
     * @param  EntityManager  $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        parent::__construct($entityManager, $entityManager->getClassMetadata($this->entityName()));
    }

    /**
     * Checks if a valid activation for the given user exists.
     *
     * @param  UserInterface  $user
     * @param  string  $code
     * @return bool
     */
    public function exists(UserInterface $user, string $code = null): bool
    {
        return (bool) $this->findIncomplete($user, $code);
    }

    /**
     * @inheritdoc
     */
    public function complete(UserInterface $user, string $code): bool
    {
        /** @var Activation $activation */
        $activation = $this->findIncomplete($user, $code);

        if ($activation === null) {
            return false;
        }

        $activation->complete();

        $this->save($activation);

        return true;
    }

    /**
     * Checks if a valid activation has been completed.
     *
     * @param  \Cartalyst\Sentinel\Users\UserInterface  $user
     * @return bool
     */
    public function completed(UserInterface $user): bool
    {
        $result = $this->findCompleted($user);

        try {
            return (bool) $result;
        } catch (NoResultException $e) {
            return false;
        }
    }

    public function findCompleted(UserInterface $user): ?Activation
    {
        $queryBuilder = $this->createQueryBuilder('a');

        $queryBuilder
            ->where('a.user = :user')
            ->andWhere('a.completed = :completed');

        $queryBuilder
            ->setParameters([
                'user' => $user,
                'completed' => true,
            ]);

        try {
            return $queryBuilder->getQuery()->getSingleResult();
        } catch (NoResultException $e) {
            return null;
        }
    }

    /**
     * @inheritdoc
     */
    public function remove(UserInterface $user): ?bool
    {
        $activation = $this->findCompleted($user);

        if (! $activation) {
            return false;
        }

        $entityManager = $this->getEntityManager();

        $entityManager->remove($activation);
        $entityManager->flush();

        return true;
    }

    /**
     * @inheritdoc
     */
    public function removeExpired(): bool
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder
            ->delete($this->entityName(), 'a')
            ->where('a.completed = :completed')
            ->andWhere('a.createdAt < :expires');

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
     * Get the Activation class name.
     *
     * @return string
     */
    abstract protected function entityName();

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
     * @return Activation|null
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function findIncomplete(UserInterface $user, $code = null)
    {
        $queryBuilder = $this->createQueryBuilder('a');

        $queryBuilder
            ->where('a.user = :user')
            ->andWhere('a.completed = :completed')
            ->andWhere('a.createdAt > :expires');

        $queryBuilder
            ->setParameter('user', $user)
            ->setParameter('completed', false)
            ->setParameter('expires', $this->expires());

        if ($code) {
            $queryBuilder
                ->andWhere('a.code = :code')
                ->setParameter('code', $code);
        }

        try {
            return $queryBuilder->getQuery()->getSingleResult();
        } catch (NoResultException $e) {
            return null;
        }
    }

    /**
     * @param  Activation  $activation
     */
    protected function save(Activation $activation)
    {
        $entityManager = $this->getEntityManager();

        $entityManager->persist($activation);
        $entityManager->flush();
    }
}
