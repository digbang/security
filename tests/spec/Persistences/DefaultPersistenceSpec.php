<?php

namespace spec\Digbang\Security\Persistences;

use Digbang\Security\Users\User;
use PhpSpec\ObjectBehavior;

/**
 * Class DefaultPersistenceSpec.
 *
 * @mixin \Digbang\Security\Persistences\DefaultPersistence
 */
class DefaultPersistenceSpec extends ObjectBehavior
{
    public function let(User $user)
    {
        $this->beConstructedWith($user, str_random(32));
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Digbang\Security\Persistences\DefaultPersistence');
    }

    public function it_is_a_persistence()
    {
        $this->shouldHaveType('Digbang\Security\Persistences\Persistence');
    }

    public function it_should_hold_a_ref_to_the_user(User $user)
    {
        $this->getUser()->shouldBe($user);
    }

    public function it_should_hold_a_ref_to_the_code(User $user)
    {
        $this->beConstructedWith($user, $code = str_random(32));

        $this->getCode()->shouldBe($code);
    }
}
