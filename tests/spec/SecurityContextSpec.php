<?php namespace spec\Digbang\Security;

use Illuminate\Contracts\Container\Container;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class SecurityContextSpec
 *
 * @package spec\Digbang\Security
 * @mixin \Digbang\Security\SecurityContext
 */
class SecurityContextSpec extends ObjectBehavior
{
	function let(Container $container)
	{
		$this->beConstructedWith($container);
	}

    function it_is_initializable()
    {
        $this->shouldHaveType('Digbang\Security\SecurityContext');
    }
}
