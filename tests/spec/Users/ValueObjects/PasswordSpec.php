<?php namespace spec\Digbang\Security\Users\ValueObjects;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class PasswordSpec
 *
 * @package spec\Digbang\Security\Users\ValueObjects
 * @mixin \Digbang\Security\Users\ValueObjects\Password
 */
class PasswordSpec extends ObjectBehavior
{
    function let()
    {
	    $this->beConstructedWith('rubbish');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Digbang\Security\Users\ValueObjects\Password');
    }

	function it_should_check_the_password_with_native_php_functions()
	{
		$this->check('garbage')->shouldBe(false);
		$this->check('rubbish')->shouldBe(true);
	}

	function it_should_expose_the_hash()
	{
		// should it?
		$this->getHash()->shouldMatch('/^.{60}$/');
	}
}
