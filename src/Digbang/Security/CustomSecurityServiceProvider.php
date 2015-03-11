<?php namespace Digbang\Security;

use Cartalyst\Sentry\Hashing;
use Cartalyst\Sentry\Cookies\IlluminateCookie;
use Cartalyst\Sentry\Sentry;
use Cartalyst\Sentry\Sessions\IlluminateSession;
use Cartalyst\Sentry\Users\ProviderInterface as UserProvider;
use Cartalyst\Sentry\Groups\ProviderInterface as GroupProvider;
use Cartalyst\Sentry\Throttling\ProviderInterface as ThrottleProvider;
use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider;

class CustomSecurityServiceProvider extends ServiceProvider
{
	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerHasher();
		$this->registerUserProvider();
		$this->registerGroupProvider();
		$this->registerThrottleProvider();
		$this->registerSession();
		$this->registerCookie();
		$this->registerSentry();
	}

	/**
	 * Register the hasher used by Sentry.
	 *
	 * @return void
	 */
	protected function registerHasher()
	{
		$this->app['sentry.hasher'] = $this->app->share(
			function ($app)
			{
				$hasher = $app['config']['cartalyst/sentry::hasher'];

				switch ($hasher)
				{
					case 'native':
						return new Hashing\NativeHasher;
						break;

					case 'bcrypt':
						return new Hashing\BcryptHasher;
						break;

					case 'sha256':
						return new Hashing\Sha256Hasher;
						break;

					case 'whirlpool':
						return new Hashing\WhirlpoolHasher;
						break;
				}

				throw new \InvalidArgumentException("Invalid hasher [$hasher] chosen for Sentry.");
			}
		);

		$this->app->bind(Hashing\HasherInterface::class, function (Container $app){
			return $app['sentry.hasher'];
		});
	}

	/**
	 * Register the user provider used by Sentry.
	 *
	 * @return void
	 */
	protected function registerUserProvider()
	{
		$this->app['sentry.user'] = $this->app->share(function ($app){
			if (isset($this->app[UserProvider::class]))
			{
				return $this->app[UserProvider::class];
			}

			throw new \UnexpectedValueException("User provider not configured.");
		});
	}

	/**
	 * Register the group provider used by Sentry.
	 *
	 * @return void
	 */
	protected function registerGroupProvider()
	{
		$this->app['sentry.group'] = $this->app->share(function ($app){
			if (isset($this->app[GroupProvider::class]))
			{
				return $this->app[GroupProvider::class];
			}

			throw new \UnexpectedValueException("Group provider not configured.");
		});
	}

	/**
	 * Register the throttle provider used by Sentry.
	 *
	 * @return void
	 */
	protected function registerThrottleProvider()
	{
		$this->app['sentry.throttle'] = $this->app->share(function ($app){
			if (isset($this->app[ThrottleProvider::class]))
			{
				return $this->app[ThrottleProvider::class];
			}

			throw new \UnexpectedValueException("Throttling provider not configured.");
		});
	}

	/**
	 * Register the session driver used by Sentry.
	 *
	 * @return void
	 */
	protected function registerSession()
	{
		$this->app['sentry.session'] = $this->app->share(function ($app){
			$key = $app['config']['cartalyst/sentry::cookie.key'];

			return new IlluminateSession($app['session.store'], $key);
		});
	}

	/**
	 * Register the cookie driver used by Sentry.
	 *
	 * @return void
	 */
	protected function registerCookie()
	{
		$this->app['sentry.cookie'] = $this->app->share(function ($app){
			$key = $app['config']['cartalyst/sentry::cookie.key'];

			/**
			 * We'll default to using the 'request' strategy, but switch to
			 * 'jar' if the Laravel version in use is 4.0.*
			 */

			$strategy = 'request';

			if (preg_match('/^4\.0\.\d*$/D', $app::VERSION))
			{
				$strategy = 'jar';
			}

			return new IlluminateCookie($app['request'], $app['cookie'], $key, $strategy);
		});
	}

	/**
	 * Takes all the components of Sentry and glues them
	 * together to create Sentry.
	 *
	 * @return void
	 */
	protected function registerSentry()
	{
		$this->app['sentry'] = $this->app->share(function ($app){
			return new Sentry(
				$app['sentry.user'],
				$app['sentry.group'],
				$app['sentry.throttle'],
				$app['sentry.session'],
				$app['sentry.cookie'],
				$app['request']->getClientIp()
			);
		});
	}
}
