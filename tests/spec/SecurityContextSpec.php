<?php

namespace spec\Digbang\Security;

use Illuminate\Contracts\Container\Container;
use PhpSpec\ObjectBehavior;

/**
 * Class SecurityContextSpec.
 *
 * @mixin \Digbang\Security\SecurityContext
 */
class SecurityContextSpec extends ObjectBehavior
{
    public function let(Container $container)
    {
        $this->beConstructedWith($container);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Digbang\Security\SecurityContext');
    }
}
