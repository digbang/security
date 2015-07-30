<?php namespace Digbang\Security\Laravel;

use Digbang\Security\Contracts\Factories\RepositoryFactory;
use Digbang\Security\Factories\DefaultRepositoryFactory;
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
}
