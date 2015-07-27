<?php namespace spec\Digbang\Security\Repositories;

use Cartalyst\Sentinel\Roles\RoleRepositoryInterface;
use Digbang\Security\Entities\Role;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Persisters\Entity\EntityPersister;
use Doctrine\ORM\UnitOfWork;
use Illuminate\Config\Repository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class DoctrineGroupRepositorySpec
 *
 * @package spec\Digbang\Security\Repositories
 * @mixin \Digbang\Security\Repositories\DoctrineGroupRepository
 */
class DoctrineGroupRepositorySpec extends ObjectBehavior
{
    function let(EntityManager $em, ClassMetadata $cm, UnitOfWork $uow, EntityPersister $ep, Repository $config)
    {
	    $config->get('digbang.security.auth.groups.model', Role::class)->willReturn(Role::class);
        $group = new Role('Testing Group');

        $cm->name = Role::class;

        $em->getClassMetadata(Role::class)->willReturn($cm);
        $em->getUnitOfWork()->willReturn($uow);
        $uow->getEntityPersister(Role::class)->willReturn($ep);

        // Successful find by ID
        $em->find(Role::class, 1, Argument::cetera())->willReturn($group);
        // Successful find by name/slug
        $ep->load(['name' => 'Testing Group'], Argument::cetera())->willReturn($group);
        $ep->load(['slug' => 'testing-group'], Argument::cetera())->willReturn($group);

        // Failed to find by id
        $em->find(Role::class, Argument::not(1), Argument::cetera())->willReturn(null);
        // Failed to find by everything else
        $ep->load(Argument::cetera())->willReturn(null);

        $this->beConstructedWith($em, $config);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Digbang\Security\Repositories\DoctrineGroupRepository');
    }

    function it_should_implement_sentrys_provider_interface()
    {
        $this->shouldHaveType(RoleRepositoryInterface::class);
    }

    function it_should_find_groups_by_id()
    {
        $this->findById(1)->shouldBeAnInstanceOf(Role::class);
    }

    function it_should_fail_when_the_group_is_not_found_by_id()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->duringFindById(2);
    }

    function it_should_find_groups_by_slug()
    {
        $this->findBySlug('testing-group')->shouldBeAnInstanceOf(Role::class);
    }

    function it_should_fail_when_the_group_is_not_found_by_slug()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->duringFindById('this_group_is_fake');
    }

    function it_should_find_groups_by_name()
    {
        $this->findByName('Testing Group')->shouldBeAnInstanceOf(Role::class);
    }

    function it_should_fail_when_the_group_is_not_found_by_name()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->duringFindByName('Sarasa');
    }

    function it_should_create_groups(EntityManager $em)
    {
        /** @type Double $em */
        $em->persist(Argument::type(Role::class))->shouldBeCalled();
        $em->flush()->shouldBeCalled();

        $this->create([
            'name' => 'Ninjas'
        ])->shouldBeAnInstanceOf(Role::class);
    }

    function it_should_save_groups(EntityManager $em)
    {
        $group = new Role('Samurais');

        /** @type Double $em */
        $em->persist($group)->shouldBeCalled();
        $em->flush()->shouldBeCalled();

        $this->save($group);
    }

    function it_should_delete_groups(EntityManager $em)
    {
        $group = new Role('Samurais');

        /** @type Double $em */
        $em->remove($group)->shouldBeCalled();
        $em->flush()->shouldBeCalled();

        $this->delete($group);
    }
}
