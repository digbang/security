<?php

namespace Digbang\Security\Activations;

use Carbon\Carbon;
use Cartalyst\Sentinel\Users\UserInterface;
use Digbang\Security\Users\User;
use Digbang\Security\Users\UserRepository;
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
     * @var UserRepository
     */
    private $users;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager, UserRepository $users)
    {
        parent::__construct($entityManager, $entityManager->getClassMetadata($this->entityName()));
        $this->users = $users;
    }

    /**
     * Checks if a valid activation for the given user exists.
     *
     * @param  UserInterface  $user
     * @param  string         $code
     *
     * @return bool
     */
    public function exists(UserInterface $user, string $code = null): bool
    {
        return (bool) $this->findIncomplete($user, $code);
    }

    /**
     * {@inheritdoc}
     */
    public function complete(UserInterface $user, string $code): bool
    {
        /** @var Activation $activation */
        $activation = $this->findIncomplete($user, $code);

        if ($activation === null) {
            return false;
        }

        if ($user instanceof User && ! $user->canActivate()){
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
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * @param UserInterface $user
     * @param string|null   $code
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return Activation|null
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
     * @param Activation $activation
     */
    protected function save(Activation $activation)
    {
        $entityManager = $this->getEntityManager();

        $entityManager->persist($activation);
        $entityManager->flush();
    }

    /**
     * Confirm the password and activate the user
     *
     * @param UserInterface $user
     * @param string $code
     * @param string $password
     * @return bool
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function completeAndUpdatePassword(UserInterface $user, string $code, string $password): bool
    {
        $result = $this->findIncomplete($user, $code);

        if ($result === null) {
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

            $result->complete();
            $this->save($result);

            $entityManager->commit();

            return true;
        } catch (\Exception $e) {
            $entityManager->rollback();

            return false;
        }
    }
}
