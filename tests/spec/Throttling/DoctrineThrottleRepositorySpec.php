<?php namespace spec\Digbang\Security\Throttling;
use Digbang\Security\Contracts\Factories\ThrottleFactory;
use Digbang\Security\Entities\User;
use Digbang\Security\Throttling\Throttle;
use Digbang\Security\Throttling\UserThrottle;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Persisters\Entity\EntityPersister;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\UnitOfWork;
use Illuminate\Contracts\Config\Repository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class DoctrineThrottleRepositorySpec
 *
 * @package spec\Digbang\Security\Repositories
 * @mixin \Digbang\Security\Throttling\DoctrineThrottleRepository
 */
class DoctrineThrottleRepositorySpec extends ObjectBehavior
{
    private $user;

    function let(
        EntityManager $em,
        ClassMetadata $cm,
        UnitOfWork $uow,
        EntityPersister $ep,
		Repository $config,
		QueryBuilder $qb,
	    AbstractQuery $query,
		ThrottleFactory $throttleFactory
    )
    {
	    $config->get('digbang.security.auth.throttling.model', Throttle::class)->willReturn(Throttle::class);
	    $this->user = new User('testing', 'asd');
        $this->throttle = new UserThrottle($this->user);

        $cm->name = Throttle::class;
        $em->getClassMetadata(Throttle::class)->willReturn($cm);
        $em->getUnitOfWork()->willReturn($uow);
	    $em->createQueryBuilder()->willReturn($qb);
        $uow->getEntityPersister(Throttle::class)->willReturn($ep);

	    /** @type Collaborator $qb */
	    $qb->select(Argument::cetera())->willReturn($qb);
	    $qb->from(Argument::cetera())->willReturn($qb);
	    $qb->where(Argument::cetera())->willReturn($qb);
	    $qb->andWhere(Argument::cetera())->willReturn($qb);
	    $qb->addCriteria(Argument::cetera())->willReturn($qb);
	    $qb->setMaxResults(Argument::cetera())->willReturn($qb);
	    $qb->getQuery()->willReturn($query);
	    $query->getSingleResult()->willReturn($this->throttle);

        // Successful find by ID
        $em->find(Throttle::class, 1, Argument::cetera())->willReturn($this->throttle);
        // Successful find by user
        $ep->load([Criteria::expr()->eq('user', $this->user)], Argument::cetera())->willReturn($this->throttle);
        // Successful find by user and ip address
        $ep->load(Argument::allOf(
            Argument::containing(Criteria::expr()->eq('user', $this->user)),
            Argument::containing(Argument::type(CompositeExpression::class))
        ), Argument::cetera())->willReturn($this->throttle);
        // Successful find collection by group / permissions
        $ep->loadAll(Argument::cetera())->willReturn([$this->throttle]);

//	    $em->persist(Argument::any())->willReturn(true);
//	    $em->flush(Argument::any())->willReturn(true);
        // Failed to find by id
        $em->find(Throttle::class, Argument::not(1), Argument::cetera())->willReturn(null);
        // Failed to find by everything else
        $ep->load(Argument::cetera())->willReturn(null);

        $this->beConstructedWith($em, $config, $throttleFactory);
    }


    function it_is_initializable()
    {
        $this->shouldHaveType('Digbang\Security\Throttling\DoctrineThrottleRepository');
    }

    function it_should_implement_sentrys_provider_interface()
    {
        $this->shouldHaveType(ThrottleRepositoryInterface::class);
    }

    function it_should_give_me_zero_for_empty_cases_of_global_delay()
    {
        $this->globalDelay()->shouldBe(0);
    }

    function it_should_give_me_zero_for_empty_cases_of_ip_delay()
    {
        $this->ipDelay('0.0.0.0')->shouldBe(0);
    }

    function it_should_give_me_zero_for_empty_cases_of_user_delay()
    {
        $this->userDelay($this->user)->shouldBe(0);
    }

    function it_should_log_throttling_entries()
    {
        // Global
        $this->log();

        // Ip
        $this->log('0.0.0.0');

        // User
        $this->log(null, $this->user);
    }
}
