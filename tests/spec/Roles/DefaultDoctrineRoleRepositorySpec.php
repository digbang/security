<?php namespace spec\Digbang\Security\Roles;

use Cartalyst\Sentinel\Roles\RoleRepositoryInterface;
use Digbang\Security\Roles\Role;
use Digbang\Security\Roles\DefaultRole;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Persisters\Entity\EntityPersister;
use Doctrine\ORM\UnitOfWork;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class DoctrineRoleRepositorySpec
 *
 * @package spec\Digbang\Security\Repositories
 * @mixin \Digbang\Security\Roles\DefaultDoctrineRoleRepository
 */
class DefaultDoctrineRoleRepositorySpec extends ObjectBehavior
{
    function let(EntityManager $em, ClassMetadata $cm, UnitOfWork $uow, EntityPersister $ep)
    {
        $role = new DefaultRole('Testing Role');

        $cm->name = DefaultRole::class;

        $em->getClassMetadata(DefaultRole::class)->willReturn($cm);
        $em->getUnitOfWork()->willReturn($uow);
        $uow->getEntityPersister(DefaultRole::class)->willReturn($ep);

        // Successful find by ID
        $em->find(DefaultRole::class, 1, Argument::cetera())->willReturn($role);
        // Successful find by name/slug
        $ep->load(['name' => 'Testing Role'], Argument::cetera())->willReturn($role);
        $ep->load(['slug' => 'testing-role'], Argument::cetera())->willReturn($role);

        // Failed to find by id
        $em->find(DefaultRole::class, Argument::not(1), Argument::cetera())->willReturn(null);
        // Failed to find by everything else
        $ep->load(Argument::cetera())->willReturn(null);

        $this->beConstructedWith($em);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Digbang\Security\Roles\DefaultDoctrineRoleRepository');
        $this->shouldHaveType('Digbang\Security\Roles\DoctrineRoleRepository');
    }

    function it_should_implement_sentinels_role_repository_interface()
    {
        $this->shouldHaveType(RoleRepositoryInterface::class);
    }

    function it_should_find_roles_by_id()
    {
        $this->findById(1)->shouldBeAnInstanceOf(Role::class);
    }

    function it_should_fail_when_the_role_is_not_found_by_id()
    {
        $this->findById(2)->shouldBe(null);
    }

    function it_should_find_roles_by_slug()
    {
        $this->findBySlug('testing-role')->shouldBeAnInstanceOf(Role::class);
    }

    function it_should_fail_when_the_role_is_not_found_by_slug()
    {
        $this->findById('this_role_is_fake')->shouldBe(null);
    }

    function it_should_find_roles_by_name()
    {
        $this->findByName('Testing Role')->shouldBeAnInstanceOf(Role::class);
    }

    function it_should_fail_when_the_role_is_not_found_by_name()
    {
        $this->findByName('Sarasa')->shouldBe(null);
    }

    function it_should_create_roles(EntityManager $em)
    {
        /** @type Double $em */
        $em->persist(Argument::type(Role::class))->shouldBeCalled();
        $em->flush()->shouldBeCalled();

        $ninjas = $this->create('Ninja devs');

	    $ninjas->shouldBeAnInstanceOf(Role::class);
        $ninjas->getName()->shouldBe('Ninja devs');
	    $ninjas->getRoleSlug()->shouldBe('ninja-devs');

        $ops = $this->create('Operators', 'ops');
	    $ops->shouldBeAnInstanceOf(Role::class);
	    $ops->getName()->shouldBe('Operators');
	    $ops->getRoleSlug()->shouldBe('ops');
    }

    function it_should_save_roles(EntityManager $em)
    {
        $role = new DefaultRole('Samurais');

        /** @type Double $em */
        $em->persist($role)->shouldBeCalled();
        $em->flush()->shouldBeCalled();

        $this->save($role);
    }

    function it_should_delete_roles(EntityManager $em)
    {
        $role = new DefaultRole('Samurais');

        /** @type Double $em */
        $em->remove($role)->shouldBeCalled();
        $em->flush()->shouldBeCalled();

        $this->delete($role);
    }
}
