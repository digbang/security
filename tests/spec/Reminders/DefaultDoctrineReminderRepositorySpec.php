<?php namespace spec\Digbang\Security\Reminders;

use Cartalyst\Sentinel\Reminders\ReminderRepositoryInterface;
use Cartalyst\Sentinel\Users\UserRepositoryInterface;
use Digbang\Security\Reminders\DefaultReminder;
use Digbang\Security\Reminders\Reminder;
use Digbang\Security\Users\User;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class DefaultDoctrineReminderRepositorySpec
 *
 * @package spec\Digbang\Security\Reminders
 * @mixin \Digbang\Security\Reminders\DefaultDoctrineReminderRepository
 */
class DefaultDoctrineReminderRepositorySpec extends ObjectBehavior
{
    function let(EntityManager $entityManager, ClassMetadata $classMetadata, UserRepositoryInterface $userRepository)
    {
	    $classMetadata->name = DefaultReminder::class;
	    $entityManager->getClassMetadata(DefaultReminder::class)->willReturn($classMetadata);

	    $this->beConstructedWith($entityManager, $userRepository, 1234);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Digbang\Security\Reminders\DefaultDoctrineReminderRepository');
    }

    function it_implements_sentinels_reminder_repository_interface()
    {
        $this->shouldHaveType(ReminderRepositoryInterface::class);
    }

	function it_should_check_if_a_reminder_exists_for_a_given_user(User $user, EntityManager $entityManager, QueryBuilder $queryBuilder, AbstractQuery $query, Reminder $reminder)
	{
		$this->prepareFindIncompleteQuery($entityManager, $queryBuilder, $query, $reminder);

		$this->exists($user)->shouldBe(true);
	}

	function it_should_check_if_a_reminder_exists_for_a_given_user_and_code(User $user, EntityManager $entityManager, QueryBuilder $queryBuilder, AbstractQuery $query, Reminder $reminder)
	{
		$this->prepareFindIncompleteQuery($entityManager, $queryBuilder, $query, $reminder);
		$queryBuilder->setParameter('code', $code = str_random(32))->shouldBeCalled()->willReturn($queryBuilder);

		$this->exists($user, $code)->shouldBe(true);
	}

	function it_shouldnt_explode_if_a_reminder_does_not_exist_for_a_given_user(User $user, EntityManager $entityManager, QueryBuilder $queryBuilder, AbstractQuery $query, Reminder $reminder)
	{
		$this->prepareFindIncompleteQuery($entityManager, $queryBuilder, $query);

		$this->exists($user)->shouldBe(false);
	}

	function it_should_complete_an_existing_reminder(User $user, EntityManager $entityManager, QueryBuilder $queryBuilder, AbstractQuery $query, Reminder $reminder, UserRepositoryInterface $userRepository)
	{
		$this->prepareFindIncompleteQuery($entityManager, $queryBuilder, $query, $reminder);

		$userRepository->validForUpdate(Argument::cetera())->willReturn(true);

		$entityManager->beginTransaction()->shouldBeCalled();
		$userRepository->update($user, ['password' => 'newpassword'])->shouldBeCalled();
		$entityManager->persist(Argument::type(Reminder::class))->shouldBeCalled();
		$entityManager->commit()->shouldBeCalled();
		$entityManager->flush()->shouldBeCalled();

		$this->complete($user, $code = str_random(32), 'newpassword')->shouldBe(true);
	}

	function it_should_fail_to_complete_an_expired_reminder(User $user, EntityManager $entityManager, QueryBuilder $queryBuilder, AbstractQuery $query, UserRepositoryInterface $userRepository)
	{
		$this->prepareFindIncompleteQuery($entityManager, $queryBuilder, $query);

		$userRepository->validForUpdate(Argument::cetera())->shouldNotBeCalled();

		$entityManager->beginTransaction()->shouldNotBeCalled();
		$userRepository->update($user, ['password' => 'newpassword'])->shouldNotBeCalled();
		$entityManager->persist(Argument::type(Reminder::class))->shouldNotBeCalled();
		$entityManager->commit()->shouldNotBeCalled();
		$entityManager->flush()->shouldNotBeCalled();

		$this->complete($user, $code = str_random(32), 'newpassword')->shouldBe(false);
	}

	function it_should_remove_expired_reminders(EntityManager $entityManager, QueryBuilder $queryBuilder, AbstractQuery $query)
	{
		$entityManager->createQueryBuilder()->willReturn($queryBuilder);

		$queryBuilder->delete(Argument::cetera())->shouldBeCalled()->willReturn($queryBuilder);
		$queryBuilder->from(DefaultReminder::class, Argument::any())->shouldBeCalled()->willReturn($queryBuilder);
		$queryBuilder->where(Argument::cetera())->shouldBeCalled()->willReturn($queryBuilder);
		$queryBuilder->andWhere(Argument::cetera())->willReturn($queryBuilder);
		$queryBuilder->setParameters(Argument::any())->willReturn($queryBuilder);

		$queryBuilder->getQuery()->willReturn($query);

		$query->getSingleScalarResult()->willReturn(1);

		$this->removeExpired()->shouldBe(1);
	}

	function it_should_catch_no_results_errors(EntityManager $entityManager, QueryBuilder $queryBuilder, AbstractQuery $query)
	{
		$entityManager->createQueryBuilder()->willReturn($queryBuilder);

		$queryBuilder->delete(Argument::cetera())->shouldBeCalled()->willReturn($queryBuilder);
		$queryBuilder->from(DefaultReminder::class, Argument::any())->shouldBeCalled()->willReturn($queryBuilder);
		$queryBuilder->where(Argument::cetera())->shouldBeCalled()->willReturn($queryBuilder);
		$queryBuilder->andWhere(Argument::cetera())->willReturn($queryBuilder);
		$queryBuilder->setParameters(Argument::any())->willReturn($queryBuilder);

		$queryBuilder->getQuery()->willReturn($query);

		$query->getSingleScalarResult()->willThrow(NoResultException::class);

		$this->removeExpired()->shouldBe(0);
	}

	protected function prepareFindIncompleteQuery(EntityManager $entityManager, QueryBuilder $queryBuilder, AbstractQuery $query, $result = null)
	{
		$entityManager->createQueryBuilder()->willReturn($queryBuilder);

		$queryBuilder->select(Argument::cetera())->willReturn($queryBuilder);
		$queryBuilder->from(Argument::cetera())->willReturn($queryBuilder);
		$queryBuilder->where(Argument::cetera())->willReturn($queryBuilder);
		$queryBuilder->andWhere(Argument::cetera())->willReturn($queryBuilder);
		$queryBuilder->setParameter(Argument::cetera())->willReturn($queryBuilder);
		$queryBuilder->setParameters(Argument::cetera())->willReturn($queryBuilder);

		$queryBuilder->getQuery()->willReturn($query);

		if ($result)
		{
			$query->getSingleResult()->willReturn($result);
		}
		else
		{
			$query->getSingleResult()->willThrow(NoResultException::class);
		}
	}
}
