<?php namespace Digbang\Security\Laravel;

use Digbang\Doctrine\Metadata\DecoupledMappingDriver;
use Digbang\Security\Factories\RepositoryFactory;
use Digbang\Security\Factories\DefaultRepositoryFactory;
use Digbang\Security\Mappings\EmailMapping;
use Digbang\Security\Mappings\NameMapping;
use Digbang\Security\Mappings\PasswordMapping;
use Digbang\Security\SecurityContext;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class SecurityServiceProvider extends ServiceProvider
{
	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->singleton(SecurityContext::class);
		$this->app->bind(RepositoryFactory::class, DefaultRepositoryFactory::class);
	}

	/**
	 * Boot the service provider.
	 *
	 * @param DecoupledMappingDriver $mappingDriver
	 * @param Router                 $router
	 */
	public function boot(DecoupledMappingDriver $mappingDriver, Router $router)
	{
		$this->addMappings($mappingDriver);
		$this->addMiddleware($router);
	}

	/**
	 * @param DecoupledMappingDriver $mappingDriver
	 *
	 * @throws \Doctrine\Common\Persistence\Mapping\MappingException
	 */
	private function addMappings(DecoupledMappingDriver $mappingDriver)
	{
		$mappingDriver->addMapping(new NameMapping);
		$mappingDriver->addMapping(new EmailMapping);
		$mappingDriver->addMapping(new PasswordMapping);
	}

	/**
	 * @param Router $router
	 */
	private function addMiddleware(Router $router)
	{
		$router->middleware('security', Middleware\SecurityMiddleware::class);
	}
}
