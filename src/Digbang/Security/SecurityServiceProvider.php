<?php namespace Digbang\Security;

use Cartalyst\Sentry\Sentry;
use Digbang\Security\Commands\MigrationsCommand;
use Digbang\Security\Permissions\InsecurePermissionRepository;
use Digbang\Security\Permissions\PermissionRepository;
use Illuminate\Support\ServiceProvider;

class SecurityServiceProvider extends ServiceProvider
{
	/**
	 * @type \Illuminate\Container\Container
	 */
	protected $app;
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

		$this->app->share(Sentry::class, function(){
			return $this->app['sentry'];
		});
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
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
		return ['digbang/security'];
	}

	protected function registerCommands()
	{
		$this->app['security.migrations'] = $this->app->share(function(){
			return new MigrationsCommand();
		});

		$this->commands('security.migrations');
	}

	protected function registerPermissionRepository()
	{
		$this->app->bind(PermissionRepository::class, function(){
			$config = $this->app['config'];

			return $this->app->make($config->get(
				'security::permissions.repository',
				InsecurePermissionRepository::class
			));
		});
	}
}
