<?php namespace Digbang\Security\Laravel;

use Digbang\Doctrine\Metadata\DecoupledMappingDriver;
use Digbang\Security\Contracts\Factories\RepositoryFactory;
use Digbang\Security\Factories\DefaultRepositoryFactory;
use Digbang\Security\Mappings\EmailMapping;
use Digbang\Security\Mappings\NameMapping;
use Digbang\Security\Mappings\PasswordMapping;
use Digbang\Security\SecurityContext;
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
	 * @throws \Doctrine\Common\Persistence\Mapping\MappingException
	 */
	public function boot(DecoupledMappingDriver $mappingDriver)
	{
		foreach([
			new NameMapping,
			new EmailMapping,
			new PasswordMapping
		] as $mapping)
		{
			$mappingDriver->addMapping($mapping);
		}
	}
}
