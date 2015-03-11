<?php namespace spec\Digbang\Security\Repositories;

use Cartalyst\Sentry\Groups\GroupInterface;
use Cartalyst\Sentry\Hashing\HasherInterface;
use Cartalyst\Sentry\Users\ProviderInterface;
use Cartalyst\Sentry\Users\UserNotFoundException;
use Digbang\Security\Entities\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Persisters\Entity\EntityPersister;
use Doctrine\ORM\UnitOfWork;
use Illuminate\Config\Repository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class DoctrineUserRepositorySpec
 *
 * @package spec\Digbang\Security\Repositories
 * @mixin \Digbang\Security\Repositories\DoctrineUserRepository
 */
class DoctrineUserRepositorySpec extends ObjectBehavior
{
    function let(EntityManagerInterface $em, Repository $config, HasherInterface $hasher, ClassMetadata $cm, UnitOfWork $uow, EntityPersister $ep)
    {
        $user = new User('testing', 'asd');

        $config->get('security::auth.users.model', Argument::any())->willReturn(User::class);
        $cm->name = User::class;
        $em->getClassMetadata(User::class)->willReturn($cm);
        $em->getUnitOfWork()->willReturn($uow);
        $uow->getEntityPersister(User::class)->willReturn($ep);

        // Successful find by ID
        $em->find(User::class, 1, Argument::cetera())->willReturn($user);
        // Successful find by email
        $ep->load(['email' => 'testing'], Argument::cetera())->willReturn($user);
        // Successful find by credentials
        $ep->load(['email' => 'testing', 'password' => 'asd'], Argument::cetera())->willReturn($user);
        // Successful find by activation code
        $ep->load(['activationCode' => 'validac'], Argument::cetera())->willReturn($user);
        // Successful find by reset password code
        $ep->load(['resetPasswordCode' => 'valid-code'], Argument::cetera())->willReturn($user);
        // Successful find collection by group / permissions
        $ep->loadAll(Argument::cetera())->willReturn([$user]);

        // Failed to find by id
        $em->find(User::class, Argument::not(1), Argument::cetera())->willReturn(null);
        // Failed to find by everything else
        $ep->load(Argument::cetera())->willReturn(null);

        $this->beConstructedWith($em, $config, $hasher);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Digbang\Security\Repositories\DoctrineUserRepository');
    }

    function it_should_implement_sentrys_provider_interface()
    {
        $this->shouldHaveType(ProviderInterface::class);
    }

    function it_should_find_users_by_id()
    {
        $this->findById(1)->shouldBeAnInstanceOf(User::class);
    }

    function it_should_fail_when_the_user_is_not_found_by_id()
    {
        $this->shouldThrow(UserNotFoundException::class)->duringFindById(2);
    }

    function it_should_find_users_by_login()
    {
        $this->findByLogin('testing')->shouldBeAnInstanceOf(User::class);
    }

    function it_should_fail_when_the_user_is_not_found_by_login()
    {
        $this->shouldThrow(UserNotFoundException::class)->duringFindByLogin('guiwoda@gmail.com');
    }

    function it_should_find_users_by_its_credentials(HasherInterface $hasher)
    {
	    $hasher->checkhash('asd', Argument::any())->willReturn(true);

        $this->findByCredentials([
            'email' => 'testing',
            'password' => 'asd'
        ])->shouldBeAnInstanceOf(User::class);
    }

    function it_should_fail_when_the_user_is_not_found_by_its_credentials()
    {
        $this->shouldThrow(UserNotFoundException::class)->duringFindByCredentials([
            'email' => 'guiwoda@gmail.com',
            'password' => 'yeah, sure.'
        ]);
    }

    function it_should_find_users_by_its_activation_code()
    {
        $this->findByActivationCode('validac')->shouldBeAnInstanceOf(User::class);
    }

    function it_should_fail_when_the_user_is_not_found_by_its_activation_code()
    {
        $this->shouldThrow(UserNotFoundException::class)->duringFindByActivationCode('invalid_ac');
    }

    function it_should_find_users_by_its_reset_password_code()
    {
        $this->findByResetPasswordCode('valid-code')->shouldBeAnInstanceOf(User::class);
    }

    function it_should_fail_when_the_user_is_not_found_by_its_reset_password_code()
    {
        $this->shouldThrow(UserNotFoundException::class)->duringFindByResetPasswordCode('invalid-code');
    }

    function it_should_find_a_set_of_users_by_group(GroupInterface $group)
    {
        $users = $this->findAllInGroup($group);

        $users->shouldBeAnInstanceOf(\Traversable::class);
        $users[0]->shouldBeAnInstanceOf(User::class);
    }

    function it_should_find_a_set_of_users_with_certain_permissions()
    {
        $users = $this->findAllWithAccess('a_certain_permission');

	    $users->shouldBeAnInstanceOf(\Traversable::class);
        $users[0]->shouldBeAnInstanceOf(User::class);
    }

    function it_should_find_a_set_of_users_with_any_of_certain_permissions()
    {
        $users = $this->findAllWithAnyAccess(['a_certain_permission', 'an_optional_permission']);

	    $users->shouldBeAnInstanceOf(\Traversable::class);
        $users[0]->shouldBeAnInstanceOf(User::class);
    }

    function it_should_create_users(EntityManagerInterface $em)
    {
        /** @type Double $em */
        $em->persist(Argument::type(User::class))->shouldBeCalled();
        $em->flush()->shouldBeCalled();

        $user = $this->create([
            'email' => 'guiwoda@gmail.com',
            'password' => 'my_real_password'
        ]);

        $user->shouldBeAnInstanceOf(User::class);
    }

    /** Should it? */
    function it_should_create_an_empty_user()
    {
        $this->getEmptyUser()->shouldBeAnInstanceOf(User::class);
    }

    function it_should_save_users(EntityManagerInterface $em)
    {
        $user = new User('guiwoda@gmail.com', 'my_real_password');

        /** @type Double $em */
        $em->persist($user)->shouldBeCalled();
        $em->flush()->shouldBeCalled();

        $this->save($user);
    }

    function it_should_delete_users(EntityManagerInterface $em)
    {
        $user = new User('guiwoda@gmail.com', 'my_real_password');

        /** @type Double $em */
        $em->remove($user)->shouldBeCalled();
        $em->flush()->shouldBeCalled();

        $this->delete($user);
    }

    function it_should_check_hashes(HasherInterface $hasher)
    {
        $aString = 'astring';
        $aHashedString = 'ahashedstring';

        $boolean = (boolean) rand(0,1);
        $hasher->checkhash($aString, $aHashedString)->willReturn($boolean);
        $this->checkHash($aString, $aHashedString)->shouldReturn($boolean);
    }
}
