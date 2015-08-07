<?php namespace spec\Digbang\Security\Throttling;

use Carbon\Carbon;
use Cartalyst\Sentinel\Throttling\ThrottleRepositoryInterface;
use Digbang\Security\Throttling\DefaultGlobalThrottle;
use Digbang\Security\Throttling\DefaultIpThrottle;
use Digbang\Security\Throttling\DefaultThrottle;
use Digbang\Security\Throttling\DefaultUserThrottle;
use Digbang\Security\Users\User;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\CompositeExpression;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Persisters\Entity\EntityPersister;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\UnitOfWork;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class DoctrineThrottleRepositorySpec
 *
 * @package spec\Digbang\Security\Repositories
 * @mixin \Digbang\Security\Throttling\DefaultDoctrineThrottleRepository
 */
class DefaultDoctrineThrottleRepositorySpec extends ObjectBehavior
{
    function let(
        EntityManager $entityManager,
        ClassMetadata $cm,
        UnitOfWork $uow,
        EntityPersister $ep,
		QueryBuilder $queryBuilder,
	    AbstractQuery $query,
		User $user
    )
    {
	    $cm->name = DefaultThrottle::class;
	    $entityManager->getClassMetadata(DefaultThrottle::class)->willReturn($cm);

        $throttle = new DefaultUserThrottle($user->getWrappedObject());

        $entityManager->getUnitOfWork()->willReturn($uow);
	    $entityManager->createQueryBuilder()->willReturn($queryBuilder);
        $uow->getEntityPersister(DefaultThrottle::class)->willReturn($ep);

	    /** @type Collaborator $queryBuilder */
	    $queryBuilder->select(Argument::cetera())->willReturn($queryBuilder);
	    $queryBuilder->from(Argument::cetera())->willReturn($queryBuilder);
	    $queryBuilder->where(Argument::cetera())->willReturn($queryBuilder);
	    $queryBuilder->andWhere(Argument::cetera())->willReturn($queryBuilder);
	    $queryBuilder->addCriteria(Argument::cetera())->willReturn($queryBuilder);
	    $queryBuilder->setMaxResults(Argument::cetera())->willReturn($queryBuilder);
	    $queryBuilder->setParameter(Argument::cetera())->willReturn($queryBuilder);
	    $queryBuilder->setParameters(Argument::any())->willReturn($queryBuilder);
	    $queryBuilder->getQuery()->willReturn($query);
	    $query->getSingleResult()->willReturn($throttle);

        // Successful find by ID
        $entityManager->find(DefaultThrottle::class, 1, Argument::cetera())->willReturn($throttle);
        // Successful find by user
        $ep->load([Criteria::expr()->eq('user', $user)], Argument::cetera())->willReturn($throttle);
        // Successful find by user and ip address
        $ep->load(Argument::allOf(
            Argument::containing(Criteria::expr()->eq('user', $user)),
            Argument::containing(Argument::type(CompositeExpression::class))
        ), Argument::cetera())->willReturn($throttle);
        // Successful find collection by group / permissions
        $ep->loadAll(Argument::cetera())->willReturn([$throttle]);

	    $entityManager->persist(Argument::type(DefaultThrottle::class))->willReturn(true);
	    $entityManager->flush(Argument::any())->willReturn(true);
        // Failed to find by id
        $entityManager->find(DefaultThrottle::class, Argument::not(1), Argument::cetera())->willReturn(null);
        // Failed to find by everything else
        $ep->load(Argument::cetera())->willReturn(null);

	    $this->beConstructedWith($entityManager);
    }


    function it_is_initializable()
    {
        $this->shouldHaveType('Digbang\Security\Throttling\DoctrineThrottleRepository');
    }

    function it_should_implement_sentinels_throttle_repository_interface()
    {
        $this->shouldHaveType(ThrottleRepositoryInterface::class);
    }

    function it_should_give_me_zero_for_empty_cases_of_global_delay(QueryBuilder $queryBuilder, AbstractQuery $query)
    {
        $queryBuilder->where('t.createdAt > :interval')->shouldBeCalled()->willReturn($queryBuilder);

	    $query->getResult()->willReturn([]);

        $this->globalDelay()->shouldBe(0);
    }

    function it_should_give_me_zero_for_empty_cases_of_ip_delay(AbstractQuery $query)
    {
	    $query->getResult()->willReturn([]);

        $this->ipDelay('0.0.0.0')->shouldBe(0);
    }

    function it_should_give_me_zero_for_empty_cases_of_user_delay(AbstractQuery $query, User $user)
    {
	    $user->getUserId()->willReturn(1);
	    $query->getResult()->willReturn([]);

        $this->userDelay($user)->shouldBe(0);
    }

    function it_should_log_throttling_entries(EntityManager $entityManager)
    {
	    $entityManager->persist(Argument::type(DefaultThrottle::class))
		    ->shouldBeCalledTimes(1);
	    $entityManager->flush()->shouldBeCalledTimes(1);

        $this->log();
    }

    function it_should_log_throttling_ip_entries(EntityManager $entityManager)
    {
	    $entityManager->persist(Argument::type(DefaultThrottle::class))
		    ->shouldBeCalledTimes(2);

	    $entityManager->flush()->shouldBeCalledTimes(1);

        $this->log('0.0.0.0');
    }

    function it_should_log_throttling_user_entries(User $user, EntityManager $entityManager)
    {
	    $entityManager->persist(Argument::type(DefaultThrottle::class))
		    ->shouldBeCalledTimes(2);

	    $entityManager->flush()->shouldBeCalledTimes(1);

        $this->log(null, $user);
    }

    function it_should_log_throttling_user_and_ip_entries(User $user, EntityManager $entityManager)
    {
	    $entityManager->persist(Argument::type(DefaultThrottle::class))
		    ->shouldBeCalledTimes(3);
	    $entityManager->flush()->shouldBeCalledTimes(1);

	    $this->log('127.0.0.1', $user);
    }

	function it_should_give_me_the_registered_global_delay_when_it_is_set(QueryBuilder $queryBuilder, AbstractQuery $query, DefaultGlobalThrottle $globalThrottle)
    {
	    Carbon::setTestNow($now = Carbon::now());

        $queryBuilder->where('t.createdAt > :interval')->shouldBeCalled()->willReturn($queryBuilder);

	    $rubbish = range(0, 60);
	    $rubbish[] = $globalThrottle;

	    $query->getResult()->willReturn($rubbish);

	    $globalThrottle->getCreatedAt()->shouldBeCalled()
		    ->willReturn(Carbon::createFromTimestamp($now->timestamp - 1));

        $this->globalDelay()->shouldBe(31);
    }

	function it_should_give_me_the_registered_ip_delay_when_it_is_set(QueryBuilder $queryBuilder, AbstractQuery $query, DefaultIpThrottle $ipThrottle)
    {
	    Carbon::setTestNow($now = Carbon::now());
	    $this->setIpInterval($interval = mt_rand(10, 999));

        $queryBuilder->where('t.createdAt > :interval')->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->andWhere('t.ip = :ip')->shouldBeCalled()->willReturn($queryBuilder);

	    $rubbish = range(0, 30);
	    array_unshift($rubbish, $ipThrottle);

	    $query->getResult()->willReturn($rubbish);

	    $ipThrottle->getCreatedAt()->shouldBeCalled()
		    ->willReturn(Carbon::createFromTimestamp($now->timestamp - 1));

        $this->ipDelay('127.0.0.1')->shouldBe($interval - 1);
    }

	function it_should_give_me_the_registered_user_delay_when_it_is_set(QueryBuilder $queryBuilder, AbstractQuery $query, DefaultUserThrottle $userThrottle, User $user)
    {
	    Carbon::setTestNow($now = Carbon::now());
	    $this->setUserInterval($interval = mt_rand(10, 999));

	    $user->getUserId()->shouldBeCalled()->willReturn(42);
        $queryBuilder->where('t.createdAt > :interval')->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->andWhere('t.user = :user')->shouldBeCalled()->willReturn($queryBuilder);

	    $rubbish = range(0, 30);
	    array_unshift($rubbish, $userThrottle);

	    $query->getResult()->willReturn($rubbish);

	    $userThrottle->getCreatedAt()->shouldBeCalled()
		    ->willReturn(Carbon::createFromTimestamp($now->timestamp - 1));

        $this->userDelay($user)->shouldBe($interval - 1);
    }

}
