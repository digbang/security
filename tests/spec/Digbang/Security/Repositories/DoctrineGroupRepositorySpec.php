<?php namespace spec\Digbang\Security\Repositories;

use Cartalyst\Sentry\Groups\GroupNotFoundException;
use Cartalyst\Sentry\Groups\ProviderInterface;
use Digbang\Security\Entities\Group;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManagerInterface;
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
    function let(EntityManagerInterface $em, ClassMetadata $cm, UnitOfWork $uow, EntityPersister $ep, Repository $config)
    {
	    $config->get('security::auth.groups.model', Group::class)->willReturn(Group::class);
        $group = new Group('Testing Group');

        $cm->name = Group::class;

        $em->getClassMetadata(Group::class)->willReturn($cm);
        $em->getUnitOfWork()->willReturn($uow);
        $uow->getEntityPersister(Group::class)->willReturn($ep);

        // Successful find by ID
        $em->find(Group::class, 1, Argument::cetera())->willReturn($group);
        // Successful find by name
        $ep->load(['name' => 'Testing Group'], Argument::cetera())->willReturn($group);

        // Failed to find by id
        $em->find(Group::class, Argument::not(1), Argument::cetera())->willReturn(null);
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
        $this->shouldHaveType(ProviderInterface::class);
    }

    function it_should_find_groups_by_id()
    {
        $this->findById(1)->shouldBeAnInstanceOf(Group::class);
    }

    function it_should_fail_when_the_group_is_not_found_by_id()
    {
        $this->shouldThrow(GroupNotFoundException::class)->duringFindById(2);
    }

    function it_should_find_groups_by_name()
    {
        $this->findByName('Testing Group')->shouldBeAnInstanceOf(Group::class);
    }

    function it_should_fail_when_the_group_is_not_found_by_name()
    {
        $this->shouldThrow(GroupNotFoundException::class)->duringFindByName('Sarasa');
    }

    function it_should_create_groups(EntityManagerInterface $em)
    {
        /** @type Double $em */
        $em->persist(Argument::type(Group::class))->shouldBeCalled();
        $em->flush()->shouldBeCalled();

        $this->create([
            'name' => 'Ninjas'
        ])->shouldBeAnInstanceOf(Group::class);
    }

    function it_should_save_groups(EntityManagerInterface $em)
    {
        $group = new Group('Samurais');

        /** @type Double $em */
        $em->persist($group)->shouldBeCalled();
        $em->flush()->shouldBeCalled();

        $this->save($group);
    }

    function it_should_delete_groups(EntityManagerInterface $em)
    {
        $group = new Group('Samurais');

        /** @type Double $em */
        $em->remove($group)->shouldBeCalled();
        $em->flush()->shouldBeCalled();

        $this->delete($group);
    }
}
