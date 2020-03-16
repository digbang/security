<?php

namespace spec\Digbang\Security\Persistences;

use Cartalyst\Sentinel\Cookies\CookieInterface;
use Cartalyst\Sentinel\Persistences\PersistableInterface;
use Cartalyst\Sentinel\Persistences\PersistenceRepositoryInterface;
use Cartalyst\Sentinel\Sessions\SessionInterface;
use Digbang\Security\Persistences\DefaultPersistence;
use Digbang\Security\Persistences\Persistence;
use Digbang\Security\Persistences\PersistenceRepository;
use Digbang\Security\Users\User;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Persisters\Entity\EntityPersister;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\UnitOfWork;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Prophecy\Prophet;

/**
 * Class DefaultDoctrinePersistenceRepositorySpec.
 *
 * @mixin \Digbang\Security\Persistences\DefaultDoctrinePersistenceRepository
 */
class DefaultDoctrinePersistenceRepositorySpec extends ObjectBehavior
{
    public function let(EntityManager $entityManager, ClassMetadata $classMetadata, SessionInterface $session, CookieInterface $cookie)
    {
        $classMetadata->name = DefaultPersistence::class;
        $entityManager->getClassMetadata(DefaultPersistence::class)->willReturn($classMetadata);

        $this->beConstructedWith($entityManager, $session, $cookie);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Digbang\Security\Persistences\DefaultDoctrinePersistenceRepository');
    }

    public function it_is_an_implementation_of_sentinels_persistence_repository()
    {
        $this->shouldHaveType(PersistenceRepositoryInterface::class);
        $this->shouldHaveType(PersistenceRepository::class);
    }

    public function it_should_check_for_persistences_in_session(SessionInterface $session, CookieInterface $cookie)
    {
        $session->get()->willReturn('a_code');
        $cookie->get()->shouldNotBeCalled();

        $this->check()->shouldBe('a_code');
    }

    public function it_should_check_for_persistences_in_cookies(SessionInterface $session, CookieInterface $cookie)
    {
        $session->get()->shouldBeCalled();
        $cookie->get()->shouldBeCalled()->willReturn('a_code');

        $this->check()->shouldBe('a_code');
    }

    public function it_should_return_null_for_absent_persistences(SessionInterface $session, CookieInterface $cookie)
    {
        $session->get()->shouldBeCalled();
        $cookie->get()->shouldBeCalled();

        $this->check()->shouldBe(null);
    }

    public function it_should_find_persistences_by_code(EntityManager $entityManager, UnitOfWork $unitOfWork, EntityPersister $entityPersister, Persistence $persistence)
    {
        $entityManager->getUnitOfWork()->willReturn($unitOfWork);
        $unitOfWork->getEntityPersister(DefaultPersistence::class)->willReturn($entityPersister);
        $entityPersister->load(Argument::cetera())->willReturn($persistence);

        $this->findByPersistenceCode('a_code')->shouldBe($persistence);
    }

    public function it_should_return_null_when_persistence_by_code_is_not_found(EntityManager $entityManager, UnitOfWork $unitOfWork, EntityPersister $entityPersister)
    {
        $entityManager->getUnitOfWork()->willReturn($unitOfWork);
        $unitOfWork->getEntityPersister(DefaultPersistence::class)->willReturn($entityPersister);
        $entityPersister->load(Argument::cetera())->willReturn(null);

        $this->findByPersistenceCode('a_code')->shouldBe(null);
    }

    public function it_should_find_users_by_persistence_code(EntityManager $entityManager, UnitOfWork $unitOfWork, EntityPersister $entityPersister, Persistence $persistence, User $user)
    {
        $entityManager->getUnitOfWork()->willReturn($unitOfWork);
        $unitOfWork->getEntityPersister(DefaultPersistence::class)->willReturn($entityPersister);
        $entityPersister->load(Argument::cetera())->willReturn($persistence);

        $persistence->getUser()->willReturn($user);

        $this->findUserByPersistenceCode('a_code')->shouldBe($user);
    }

    public function it_should_return_null_when_user_by_persistence_code_is_not_found(EntityManager $entityManager, UnitOfWork $unitOfWork, EntityPersister $entityPersister)
    {
        $entityManager->getUnitOfWork()->willReturn($unitOfWork);
        $unitOfWork->getEntityPersister(DefaultPersistence::class)->willReturn($entityPersister);
        $entityPersister->load(Argument::cetera())->willReturn(null);

        $this->findUserByPersistenceCode('a_code')->shouldBe(null);
    }

    public function it_should_persist_persistables(SessionInterface $session, EntityManager $entityManager)
    {
        $user = (new Prophet)->prophesize(User::class);
        $user->willImplement(PersistableInterface::class);
        $user->generatePersistenceCode()->shouldBeCalled()->willReturn($code = str_random(32));

        $session->put($code)->shouldBeCalled();

        $entityManager->persist(Argument::type(Persistence::class))->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();

        $this->persist($user)->shouldBe(true);
    }

    public function it_should_persist_and_remember_persistables(SessionInterface $session, EntityManager $entityManager, CookieInterface $cookie)
    {
        $user = (new Prophet)->prophesize(User::class);
        $user->willImplement(PersistableInterface::class);
        $user->generatePersistenceCode()->shouldBeCalled()->willReturn($code = str_random(32));

        $session->put($code)->shouldBeCalled();
        $cookie->put($code)->shouldBeCalled();

        $entityManager->persist(Argument::type(Persistence::class))->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();

        $this->persistAndRemember($user)->shouldBe(true);
    }

    public function it_should_forget_persistables(SessionInterface $session, CookieInterface $cookie, EntityManager $entityManager, QueryBuilder $queryBuilder, AbstractQuery $query)
    {
        $session->get()->willReturn($code = str_random(32));

        $session->forget()->shouldBeCalled();
        $cookie->forget()->shouldBeCalled();

        $entityManager->createQueryBuilder()->willReturn($queryBuilder);

        $queryBuilder->delete(Argument::exact(DefaultPersistence::class), Argument::any())->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->where('p.code = :code')->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->setParameter('code', $code)->shouldBeCalled()->willReturn($queryBuilder);

        $queryBuilder->getQuery()->willReturn($query);
        $query->execute(Argument::cetera())->willReturn(true);

        $this->forget()->shouldBe(true);
    }

    public function it_should_flush_a_persistable(PersistableInterface $user, SessionInterface $session, CookieInterface $cookie, EntityManager $entityManager, QueryBuilder $queryBuilder, AbstractQuery $query)
    {
        $session->get()->willReturn($code = str_random(32));

        $session->forget()->shouldBeCalled();
        $cookie->forget()->shouldBeCalled();

        $entityManager->createQueryBuilder()->willReturn($queryBuilder);

        $queryBuilder->delete(Argument::exact(DefaultPersistence::class), Argument::any())->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->where('p.code = :code')->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->where('p.user = :persistable')->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->andWhere('p.code != :code')->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->setParameter('code', $code)->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->setParameters([
            'persistable' => $user,
            'code' => $code,
        ])->shouldBeCalled()->willReturn($queryBuilder);

        $queryBuilder->getQuery()->willReturn($query);
        $query->execute(Argument::cetera())->willReturn(true);

        $this->flush($user);
    }
}
