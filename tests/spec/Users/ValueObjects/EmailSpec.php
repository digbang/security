<?php

namespace spec\Digbang\Security\Users\ValueObjects;

use PhpSpec\ObjectBehavior;

/**
 * Class EmailSpec.
 *
 * @mixin \Digbang\Security\Users\ValueObjects\Email
 */
class EmailSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('valid.email@example.com');
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Digbang\Security\Users\ValueObjects\Email');
    }

    public function it_fails_on_construction()
    {
        $this->shouldThrow('InvalidArgumentException')
            ->during('__construct', ['an invalid email']);
    }

    public function it_should_expose_the_address()
    {
        $this->getAddress()->shouldBe('valid.email@example.com');
    }
}
