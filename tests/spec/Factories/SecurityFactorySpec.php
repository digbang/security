<?php

namespace spec\Digbang\Security\Factories;

use Digbang\Security\Factories\RepositoryFactory;
use Illuminate\Contracts\Container\Container;
use Illuminate\Routing\UrlGenerator;
use PhpSpec\ObjectBehavior;

/**
 * Class SecurityFactorySpec.
 *
 * @mixin \Digbang\Security\Factories\SecurityFactory
 */
class SecurityFactorySpec extends ObjectBehavior
{
    public function let(Container $container, RepositoryFactory $repositoryFactory, UrlGenerator $url)
    {
        $this->beConstructedWith($container, $repositoryFactory, $url);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Digbang\Security\Factories\SecurityFactory');
    }
}
