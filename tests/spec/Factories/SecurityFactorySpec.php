<?php namespace spec\Digbang\Security\Factories;

use Digbang\Security\Factories\RepositoryFactory;
use Illuminate\Contracts\Container\Container;
use Illuminate\Routing\UrlGenerator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class SecurityFactorySpec
 *
 * @package spec\Digbang\Security\Factories
 * @mixin \Digbang\Security\Factories\SecurityFactory
 */
class SecurityFactorySpec extends ObjectBehavior
{
    function let(Container $container, RepositoryFactory $repositoryFactory, UrlGenerator $url)
    {
        $this->beConstructedWith($container, $repositoryFactory, $url);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Digbang\Security\Factories\SecurityFactory');
    }
}
