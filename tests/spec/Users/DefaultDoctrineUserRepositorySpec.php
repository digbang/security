<?php

namespace spec\Digbang\Security\Users;

use Cartalyst\Sentinel\Persistences\PersistenceRepositoryInterface;
use Cartalyst\Sentinel\Users\UserRepositoryInterface;
use Digbang\Security\Persistences\PersistenceRepository;
use Digbang\Security\Roles\Role;
use Digbang\Security\Roles\RoleRepository;
use Digbang\Security\Users\DefaultUser;
use Digbang\Security\Users\User;
use Digbang\Security\Users\UserRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Prophecy\Prophet;

/**
 * Class DoctrineUserRepositorySpec.
 *
 * @mixin \Digbang\Security\Users\DefaultDoctrineUserRepository
 */
class DefaultDoctrineUserRepositorySpec extends ObjectBehavior
{
    public function let(EntityManager $entityManager, PersistenceRepository $persistenceRepository, ClassMetadata $classMetadata, RoleRepository $roles)
    {
        $entityManager->getClassMetadata(DefaultUser::class)
            ->shouldBeCalled()
            ->willReturn($classMetadata);

        $classMetadata->name = DefaultUser::class;

        $this->beConstructedWith($entityManager, $persistenceRepository, $roles);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Digbang\Security\Users\DefaultDoctrineUserRepository');
        $this->shouldHaveType('Digbang\Security\Users\DoctrineUserRepository');
    }

    public function it_should_implement_sentinels_repository_interface()
    {
        $this->shouldHaveType(UserRepositoryInterface::class);
        $this->shouldHaveType(UserRepository::class);
    }

    public function it_should_find_users_by_id(EntityManager $entityManager, DefaultUser $user)
    {
        $entityManager->find(DefaultUser::class, 1, Argument::cetera())
            ->shouldBeCalled()
            ->willReturn($user);

        $this->findById(1)->shouldBe($user);
    }

    public function it_should_return_null_when_user_by_id_doesnt_find_anything(EntityManager $entityManager)
    {
        $entityManager->find(DefaultUser::class, 2, Argument::cetera())
            ->shouldBeCalled()
            ->willReturn(null);

        $this->findById(2)->shouldBe(null);
    }

    public function it_should_find_users_by_login(EntityManager $entityManager, DefaultUser $user, QueryBuilder $queryBuilder)
    {
        $this->prepareQuery($entityManager, $queryBuilder, $user->getWrappedObject());

        $this->findByCredentials([
            'login' => 'foo',
        ])->shouldBeAUser();
    }

    public function it_should_find_users_by_email(EntityManager $entityManager, DefaultUser $user, QueryBuilder $queryBuilder)
    {
        $this->prepareQuery($entityManager, $queryBuilder, $user->getWrappedObject());

        $this->findByCredentials([
            'email' => 'foo@example.com',
        ])->shouldBeAUser();
    }

    public function it_should_find_users_by_username(EntityManager $entityManager, DefaultUser $user, QueryBuilder $queryBuilder)
    {
        $this->prepareQuery($entityManager, $queryBuilder, $user->getWrappedObject());

        $this->findByCredentials([
            'username' => 'foo',
        ])->shouldBeAUser();
    }

    public function it_should_fail_when_no_login_email_or_username_is_given(EntityManager $entityManager, QueryBuilder $queryBuilder)
    {
        $this->prepareQuery($entityManager, $queryBuilder);

        $this->shouldThrow(\InvalidArgumentException::class)->duringFindByCredentials([
            'foo' => 'bar',
        ]);
    }

    public function it_should_find_users_by_persistence_code(PersistenceRepositoryInterface $persistenceRepository)
    {
        $persistenceRepository->findUserByPersistenceCode(Argument::any())
            ->shouldBeCalled();

        $this->findByPersistenceCode('foo');
    }

    public function it_should_record_logins_and_persist_them(EntityManager $entityManager, User $user)
    {
        $entityManager->persist($user)->shouldBeCalled();
        $entityManager->flush(Argument::cetera())->shouldBeCalled();

        $user->recordLogin()->shouldBeCalled();

        $this->recordLogin($user);
    }

    public function it_should_record_logouts_and_persist_them(EntityManager $entityManager, User $user)
    {
        $entityManager->persist($user)->shouldBeCalled();
        $entityManager->flush(Argument::cetera())->shouldBeCalled();

        $this->recordLogout($user);
    }

    public function it_should_validate_a_users_credentials(User $user)
    {
        $user->checkPassword(Argument::any())->willReturn(true);

        $this->validateCredentials($user, ['password' => 'any'])
            ->shouldReturn(true);
    }

    public function it_should_invalidate_a_users_credentials(User $user)
    {
        $user->checkPassword(Argument::any())->willReturn(false);

        $this->validateCredentials($user, ['password' => 'any'])
            ->shouldReturn(false);
    }

    public function it_should_check_credentials_needed_for_creation()
    {
        $this->validForCreation([])->shouldReturn(false);
        $this->validForCreation([
            'email' => 'foo@example.com',
        ])->shouldReturn(false);
        $this->validForCreation([
            'email' => 'foo@example.com',
            'password' => '1234',
        ])->shouldReturn(false);
        $this->validForCreation([
            'email' => 'foo@example.com',
            'password' => '1234',
            'username' => 'foo',
        ])->shouldReturn(true);
        $this->validForCreation([
            'email' => 'foo@example.com',
            'password' => '1234',
            'username' => 'foo',
            'firstName' => 'Foo',
            'lastName' => 'Bar',
        ])->shouldReturn(true);
    }

    public function it_should_check_credentials_needed_for_update(User $user)
    {
        $user->getUserId()->willReturn(1);

        $this->validForUpdate($user, [])->shouldReturn(true);
        $this->validForUpdate($user, [
            'email' => 'foo@example.com',
        ])->shouldReturn(true);
        $this->validForUpdate($user, [
            'email' => 'foo@example.com',
            'password' => '1234',
        ])->shouldReturn(true);
        $this->validForUpdate($user, [
            'email' => 'foo@example.com',
            'password' => '1234',
            'username' => 'foo',
        ])->shouldReturn(true);
        $this->validForUpdate($user, [
            'email' => 'foo@example.com',
            'password' => '1234',
            'username' => 'foo',
            'firstName' => 'Foo',
            'lastName' => 'Bar',
        ])->shouldReturn(true);
    }

    public function it_should_create_users(EntityManager $entityManager)
    {
        $entityManager->persist(Argument::type(DefaultUser::class))->shouldBeCalled();
        $entityManager->flush(Argument::cetera())->shouldBeCalled();

        $this->create([
            'email' => 'foo@example.com',
            'username' => 'foo',
            'password' => '1234',
        ])->shouldBeAUser();
    }

    public function it_should_update_users(EntityManager $entityManager, DefaultUser $user, RoleRepository $roles, Role $role)
    {
        $entityManager->persist($user)->shouldBeCalled();
        $entityManager->flush(Argument::cetera())->shouldBeCalled();
        $roles->findBySlug('foo')->shouldBeCalled()
            ->willReturn($role);

        $user->getRoles()->shouldBeCalled()->willReturn([]);
        $user->update([
            'email' => 'foo@example.org',
        ])->shouldBeCalled();

        $user->addRole($role)->shouldBeCalled();

        $this->update($user, [
            'email' => 'foo@example.org',
            'roles' => ['foo'],
        ])->shouldBe($user);
    }

    public function getMatchers(): array
    {
        return [
            'beAUser' => function ($subject) {
                return
                    $subject instanceof User &&
                    $subject instanceof DefaultUser;
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

        $queryBuilder->expr()->willReturn(new Expr());
        $queryBuilder->getRootAliases()->willReturn(['u']);
        $queryBuilder->select(Argument::any())->willReturn($queryBuilder);
        $queryBuilder->from(Argument::cetera())->willReturn($queryBuilder);
        $queryBuilder->where(Argument::any())->willReturn($queryBuilder);
        $queryBuilder->andWhere(Argument::any())->willReturn($queryBuilder);
        $queryBuilder->setParameter(Argument::cetera())->willReturn($queryBuilder);
        $queryBuilder->setParameters(Argument::cetera())->willReturn($queryBuilder);
        $queryBuilder->addCriteria(Argument::type(Criteria::class))->willReturn($queryBuilder);
        $queryBuilder->setMaxResults(Argument::type('int'))->willReturn($queryBuilder);

        $queryBuilder->getQuery()->willReturn($query);

        $query->getOneOrNullResult()->willReturn($result);
    }
}
