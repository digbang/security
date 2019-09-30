<?php

namespace spec\Digbang\Security\Users\ValueObjects;

use PhpSpec\ObjectBehavior;

/**
 * Class NameSpec.
 *
 * @mixin \Digbang\Security\Users\ValueObjects\Name
 */
class NameSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType('Digbang\Security\Users\ValueObjects\Name');
    }

    public function it_should_expose_the_first_name()
    {
        $this->beConstructedWith($firstName = 'John');
        $this->getFirstName()->shouldBe($firstName);
    }

    public function it_should_expose_the_last_name()
    {
        $this->beConstructedWith('', $lastName = 'Doe');
        $this->getLastName()->shouldBe($lastName);
    }

    public function it_should_expose_the_full_name()
    {
        $this->beConstructedWith($firstName = 'John', $lastName = 'Doe');
        $this->getFullName()->shouldBe("$firstName $lastName");
    }

    public function it_should_expose_the_full_name_with_custom_separators()
    {
        $this->beConstructedWith($firstName = 'John', $lastName = 'Doe');
        $this->getFullName(' William ')->shouldBe("$firstName William $lastName");
    }
}
