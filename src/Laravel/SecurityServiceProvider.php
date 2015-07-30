<?php namespace Digbang\Security\Laravel;

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
		$this->loadTranslationsFrom("$basePath/lang", 'digbang.security');
		$this->loadViewsFrom("$basePath/views",       'digbang.security');
	}
}
