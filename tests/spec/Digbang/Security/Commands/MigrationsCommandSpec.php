<?php

namespace spec\Digbang\Security\Commands;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MigrationsCommandSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Digbang\Security\Commands\MigrationsCommand');
        $this->shouldHaveType('Illuminate\Console\Command');
    }
}
