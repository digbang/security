<?php namespace Digbang\Security;

use Digbang\Security\Commands\InstallCommand;
use Illuminate\Support\ServiceProvider;

class SecurityServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('digbang/security');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->register('Cartalyst\Sentry\SentryServiceProvider');

		$this->registerUser();
		$this->registerCommands();
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return ['digbang/security', 'cartalyst/sentry'];
	}

	protected function registerUser()
	{
		$this->app->bind('Cartalyst\Sentry\Users\UserInterface', function($app){
			return $app['sentry']->getUser();
		});
	}
	protected function registerCommands()
	{
		$this->app['security.install'] = $this->app->share(function ($app)
		{
			return new InstallCommand();
		});

		$this->commands('security.install');
	}
}
