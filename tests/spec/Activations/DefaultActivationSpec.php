<?php

namespace spec\Digbang\Security\Activations;

use Carbon\Carbon;
use Digbang\Security\Users\User;
use PhpSpec\ObjectBehavior;

class DefaultActivationSpec extends ObjectBehavior
{
    public function let(User $user)
    {
        $this->beConstructedWith($user);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Digbang\Security\Activations\DefaultActivation');
    }

    public function it_should_have_a_magic_getter_for_the_code()
    {
        $this->__get('code')->shouldBeString();
        $this->__get('code')->shouldMatch('/^\w{32}$/');
    }

    public function it_should_be_marked_as_complete()
    {
        Carbon::setTestNow(Carbon::now());

        $this->complete();
        $this->getCompletedAt()->shouldBeLike(Carbon::now());
    }
}
