<?php namespace spec\Digbang\Security\Users\ValueObjects;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class EmailSpec
 *
 * @package spec\Digbang\Security\Users\ValueObjects
 * @mixin \Digbang\Security\Users\ValueObjects\Email
 */
class EmailSpec extends ObjectBehavior
{
	function let()
	{
		$this->beConstructedWith('valid.email@example.com');
	}

    function it_is_initializable()
    {
        $this->shouldHaveType('Digbang\Security\Users\ValueObjects\Email');
    }

	function it_fails_on_construction()
	{
		$this->shouldThrow('InvalidArgumentException')
			->during('__construct', ['an invalid email']);
	}

	function it_should_expose_the_address()
	{
		$this->getAddress()->shouldBe('valid.email@example.com');
	}
}
