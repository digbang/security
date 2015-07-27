<?php namespace Digbang\Security;

use Cartalyst\Sentinel\Laravel\SentinelServiceProvider;
use Digbang\Doctrine\Metadata\DecoupledMappingDriver;
use Digbang\Security\Permissions\InsecurePermissionRepository;
use Digbang\Security\Permissions\PermissionRepository;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\ServiceProvider;

class SecurityServiceProvider extends ServiceProvider
{
	/**
	 * Bootstrap the application events.
	 */
	public function boot()
	{
		$this->publishes([
			dirname(__DIR__) . '/config/config.php' => $this->app['path.config'] . '/digbang.security.php',
		], 'config');

		$this->publishes([
			dirname(__DIR__) . '/lang/' => $this->app['path.lang'] .'/vendor/security',
		], 'lang');

		$this->publishes([
			dirname(__DIR__) . '/views/' => $this->app->basePath() . '/resources/views/vendor/security',
		], 'views');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$basePath = dirname(__DIR__);
		$this->mergeConfigFrom("$basePath/config/config.php", 'digbang.security');
		$this->loadTranslationsFrom("$basePath/lang",         'digbang.security');
		$this->loadViewsFrom("$basePath/views",               'digbang.security');

		$this->app->register(SentinelServiceProvider::class);
		$this->registerConfigurations();
		$this->registerPermissionRepository();
	}

	private function registerPermissionRepository()
	{
		$this->app->singleton(PermissionRepository::class, function($app){
			/** @type Repository $config */
			$config = $app['config'];

			return $this->app->make($config->get(
				'digbang.security.permissions.repository',
				InsecurePermissionRepository::class
			));
		});
	}

	private function registerConfigurations()
	{
		$this->app->bind(Contracts\SingleMappingConfiguration::class, Configurations\SingleMappingConfiguration::class);
		$this->app->bind(Contracts\MultiMappingConfiguration::class,  Configurations\MultiMappingConfiguration::class);
	}
}
