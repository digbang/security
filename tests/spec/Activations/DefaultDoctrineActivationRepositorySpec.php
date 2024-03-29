<?php

namespace spec\Digbang\Security\Activations;

use Cartalyst\Sentinel\Activations\ActivationRepositoryInterface;
use Digbang\Security\Activations\Activation;
use Digbang\Security\Activations\ActivationRepository;
use Digbang\Security\Activations\DefaultActivation;
use Digbang\Security\Users\DefaultUser;
use Digbang\Security\Users\User;
use Digbang\Security\Users\ValueObjects\Email;
use Digbang\Security\Users\ValueObjects\Password;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Prophecy\Prophet;

/**
 * Class DefaultDoctrineActivationRepositorySpec.
 *
 * @mixin \Digbang\Security\Activations\DefaultDoctrineActivationRepository
 */
class DefaultDoctrineActivationRepositorySpec extends ObjectBehavior
{
    public function let(EntityManager $entityManager, ClassMetadata $classMetadata)
    {
        $entityManager->getClassMetadata(DefaultActivation::class)
            ->willReturn($classMetadata);

        $this->beConstructedWith($entityManager, 1234);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Digbang\Security\Activations\DefaultDoctrineActivationRepository');
    }

    public function it_is_a_sentinel_repository()
    {
        $this->shouldHaveType(ActivationRepositoryInterface::class);
        $this->shouldHaveType(ActivationRepository::class);
    }

    public function it_should_create_default_activations(User $user, EntityManager $entityManager)
    {
        $this->preparePersist($entityManager);

        $activation = $this->create($user);

        $activation->shouldBeAnActivation();
    }

    public function it_should_return_true_if_it_exists(User $user, EntityManager $entityManager, QueryBuilder $queryBuilder, Activation $activation)
    {
        $this->prepareQuery($entityManager, $queryBuilder, $activation->getWrappedObject());

        $this->exists($user)->shouldBe(true);
    }

    public function it_should_return_false_if_it_does_not_exist(User $user, EntityManager $entityManager, QueryBuilder $queryBuilder)
    {
        $this->prepareQuery($entityManager, $queryBuilder);

        $this->exists($user)->shouldBe(false);
    }

    public function it_should_find_and_complete_an_existing_activation(User $user, EntityManager $entityManager, QueryBuilder $queryBuilder)
    {
        $activation = new DefaultActivation(new DefaultUser(new Email('email@example.com'), new Password('1234'), 'a_username'));

        $this->preparePersist($entityManager);
        $this->prepareQuery($entityManager, $queryBuilder, $activation);

        $this->complete($user, '1234')->shouldBe(true);
    }

    public function it_should_return_false_for_a_not_existing_activation(User $user, EntityManager $entityManager, QueryBuilder $queryBuilder)
    {
        $this->preparePersist($entityManager, false);
        $this->prepareQuery($entityManager, $queryBuilder);

        $this->complete($user, '1234')->shouldBe(false);
    }

    public function it_should_check_if_a_complete_activation_exists(User $user, EntityManager $entityManager, QueryBuilder $queryBuilder)
    {
        $activation = new DefaultActivation(new DefaultUser(new Email('email@example.com'), new Password('1234'), 'a_username'));

        $this->prepareQuery($entityManager, $queryBuilder, $activation);

        $this->completed($user)->shouldBe(true);
    }

    public function it_should_check_if_a_complete_activation_does_not_exist(User $user, EntityManager $entityManager, QueryBuilder $queryBuilder)
    {
        $this->prepareQuery($entityManager, $queryBuilder);

        $this->completed($user)->shouldBe(false);
    }

    public function it_should_remove_an_existing_activation(User $user, EntityManager $entityManager, QueryBuilder $queryBuilder)
    {
        $activation = new DefaultActivation(new DefaultUser(new Email('email@example.com'), new Password('1234'), 'a_username'));

        $this->prepareRemove($entityManager);
        $this->prepareQuery($entityManager, $queryBuilder, $activation);

        $this->remove($user)->shouldBe(true);
    }

    public function it_should_fail_to_remove_a_non_existing_activation(User $user, EntityManager $entityManager, QueryBuilder $queryBuilder)
    {
        $this->prepareRemove($entityManager, false);
        $this->prepareQuery($entityManager, $queryBuilder);

        $this->remove($user)->shouldBe(false);
    }

    public function it_should_remove_expired_activations(EntityManager $entityManager, QueryBuilder $queryBuilder)
    {
        $query = (new Prophet())->prophesize(AbstractQuery::class);

        $entityManager->createQueryBuilder()->shouldBeCalled()->willReturn($queryBuilder);

        $queryBuilder->delete(DefaultActivation::class, Argument::any())->willReturn($queryBuilder);
        $queryBuilder->where(Argument::any())->willReturn($queryBuilder);
        $queryBuilder->andWhere(Argument::any())->willReturn($queryBuilder);
        $queryBuilder->setParameters(Argument::cetera())->willReturn($queryBuilder);

        $queryBuilder->getQuery()->willReturn($query);

        $query->getSingleScalarResult()->willReturn($amount = mt_rand(1, 20));

        $this->removeExpired()->shouldReturn(true);
    }

    public function getMatchers(): array
    {
        return [
            'beAnActivation' => function ($subject) {
                return
                    $subject instanceof Activation &&
                    $subject instanceof DefaultActivation;
            },
        ];
    }

    /**
     * @param  EntityManager  $entityManager
     * @param  QueryBuilder  $queryBuilder
     */
    private function prepareQuery(EntityManager $entityManager, QueryBuilder $queryBuilder, $result = null)
    {
        $query = (new Prophet())->prophesize(AbstractQuery::class);

        $entityManager->createQueryBuilder()->shouldBeCalled()->willReturn($queryBuilder);

        $queryBuilder->select(Argument::any())->willReturn($queryBuilder);
        $queryBuilder->from(Argument::cetera())->willReturn($queryBuilder);
        $queryBuilder->where(Argument::any())->willReturn($queryBuilder);
        $queryBuilder->andWhere(Argument::any())->willReturn($queryBuilder);
        $queryBuilder->setParameter(Argument::cetera())->willReturn($queryBuilder);
        $queryBuilder->setParameters(Argument::cetera())->willReturn($queryBuilder);

        $queryBuilder->getQuery()->willReturn($query);

        if ($result) {
            $query->getSingleResult()->willReturn($result);
        } else {
            $query->getSingleResult()->willThrow(NoResultException::class);
        }
    }

    /**
     * @param  EntityManager  $entityManager
     * @param  bool  $should
     */
    private function preparePersist(EntityManager $entityManager, $should = true)
    {
        if ($should) {
            $entityManager->persist(Argument::type(DefaultActivation::class))->shouldBeCalled();
            $entityManager->flush()->shouldBeCalled();
        } else {
            $entityManager->persist(Argument::type(DefaultActivation::class))->shouldNotBeCalled();
            $entityManager->flush()->shouldNotBeCalled();
        }
    }

    /**
     * @param  EntityManager  $entityManager
     */
    private function prepareRemove(EntityManager $entityManager, $should = true)
    {
        if ($should) {
            $entityManager->remove(Argument::type(DefaultActivation::class))->shouldBeCalled();
            $entityManager->flush()->shouldBeCalled();
        } else {
            $entityManager->remove(Argument::type(DefaultActivation::class))->shouldNotBeCalled();
            $entityManager->flush()->shouldNotBeCalled();
        }
    }
}
