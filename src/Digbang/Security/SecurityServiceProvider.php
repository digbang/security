<?php namespace Digbang\Security;

use Digbang\Security\Commands\MigrationsCommand;
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

		$this->overrideSentry();
	}

	protected function overrideSentry()
	{
		foreach ($this->app['config']['security::auth'] as $key => $val)
		{
			$this->app['config']["cartalyst/sentry::$key"] = $val;
		}
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
		$this->registerPermissionRepository();
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
		$this->app['security.migrations'] = $this->app->share(function ($app)
		{
			return new MigrationsCommand();
		});

		$this->commands('security.migrations');
	}

	protected function registerPermissionRepository()
	{
		$this->app->bind('Digbang\\Security\\Permissions\\PermissionRepository', function($app){
			return $app->make(\Config::get('security::permissions.repository'));
		});
	}
}
