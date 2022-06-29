<?php

namespace Digbang\Security\Laravel;

use Digbang\Security\Factories\ConfigurationRepositoryFactory;
use Digbang\Security\Factories\ContainerBindingRepositoryFactory;
use Digbang\Security\Factories\DefaultRepositoryFactory;
use Digbang\Security\Factories\RepositoryFactory;
use Digbang\Security\Mappings\EmailMapping;
use Digbang\Security\Mappings\NameMapping;
use Digbang\Security\Mappings\PasswordMapping;
use Digbang\Security\SecurityContext;
use Digbang\Security\Urls\PermissionAwareUrlGeneratorExtension;
use Doctrine\ORM\Mapping\MappingException;
use Doctrine\Persistence\ManagerRegistry;
use Illuminate\Contracts\Container\Container;
use Illuminate\Routing\RouteCollectionInterface;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use LaravelDoctrine\Fluent\FluentDriver;

class SecurityServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app->singleton(SecurityContext::class);
        $this->app->bind(RepositoryFactory::class, function (Container $container) {
            return
                new ContainerBindingRepositoryFactory($container,
                    new ConfigurationRepositoryFactory($container,
                        new DefaultRepositoryFactory($container)
                    )
                );
        });
    }

    /**
     * Boot the service provider.
     *
     * @param  SecurityContext  $securityContext
     * @param  Router  $router
     * @param  ManagerRegistry  $managerRegistry
     *
     * @throws MappingException
     */
    public function boot(SecurityContext $securityContext, Router $router, ManagerRegistry $managerRegistry)
    {
        /** @var EntityManager $entityManager */
        foreach ($managerRegistry->getManagers() as $entityManager) {
            $this->addMappings($securityContext->getOrCreateFluentDriver($entityManager));
        }

        $this->app
            ->when(PermissionAwareUrlGeneratorExtension::class)
            ->needs(RouteCollectionInterface::class)
            ->give(function () use ($router) {
                return $router->getRoutes();
            });

        $this->addMiddleware($router);
    }

    /**
     * @param  FluentDriver  $mappingDriver
     *
     * @throws MappingException
     */
    private function addMappings(FluentDriver $mappingDriver)
    {
        $mappingDriver->addMapping(new NameMapping);
        $mappingDriver->addMapping(new EmailMapping);
        $mappingDriver->addMapping(new PasswordMapping);
    }

    /**
     * @param  Router  $router
     */
    private function addMiddleware(Router $router)
    {
        // Laravel >= 5.4
        $router->aliasMiddleware('security', Middleware\SecurityMiddleware::class);
    }
}
