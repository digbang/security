<?php namespace Digbang\Security;

use Cartalyst\Sentry\Hashing;
use Cartalyst\Sentry\Cookies\IlluminateCookie;
use Cartalyst\Sentry\Sentry;
use Cartalyst\Sentry\Sessions\IlluminateSession;
use Cartalyst\Sentry\Users\ProviderInterface as UserProvider;
use Cartalyst\Sentry\Groups\ProviderInterface as GroupProvider;
use Cartalyst\Sentry\Throttling\ProviderInterface as ThrottleProvider;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider;

/**
 * Class SentryWithDoctrineServiceProvider
 *
 * @package Digbang\Security
 */
class SentryWithDoctrineServiceProvider extends ServiceProvider
{
	/**
	 * @type Container
	 */
	protected $app;

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
			return $this->app[UserProvider::class];
		});

		if (! isset($this->app[UserProvider::class]))
		{
			$this->app->singleton(UserProvider::class, Repositories\DoctrineUserRepository::class);
		}
	}

	/**
	 * Register the group provider used by Sentry.
	 *
	 * @return void
	 */
	protected function registerGroupProvider()
	{
		$this->app['sentry.group'] = $this->app->share(function ($app){
			return $this->app[GroupProvider::class];
		});

		if (! isset($this->app[GroupProvider::class]))
		{
			$this->app->singleton(GroupProvider::class, Repositories\DoctrineGroupRepository::class);
		}
	}

	/**
	 * Register the throttle provider used by Sentry.
	 *
	 * @return void
	 */
	protected function registerThrottleProvider()
	{
		$this->app['sentry.throttle'] = $this->app->share(function ($app){
			return $this->app[ThrottleProvider::class];
		});

		if (! isset($this->app[ThrottleProvider::class]))
		{
			$this->app->singleton(ThrottleProvider::class, function(Container $app){
				/** @type Repositories\DoctrineThrottleRepository $provider */
				$provider = $app->make(Repositories\DoctrineThrottleRepository::class);

				/** @type Repository $config */
				$config = $app->make(Repository::class);

				if (! $config->get('cartalyst/sentry::throttling.enabled', false))
				{
					$provider->disable();
				}

				$entity = $config->get('cartalyst/sentry::throttling.model');

				if (method_exists($entity, 'setAttemptLimit'))
				{
					forward_static_call_array(
						array($entity, 'setAttemptLimit'),
						array($config->get('cartalyst/sentry::throttling.attempt_limit'))
					);
				}

				if (method_exists($entity, 'setSuspensionTime'))
				{
					forward_static_call_array(
						array($entity, 'setSuspensionTime'),
						array($config->get('cartalyst/sentry::throttling.suspension_time'))
					);
				}

				return $provider;
			});
		}
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

		$this->app->bindShared(Sentry::class, function(){
			return $this->app['sentry'];
		});
	}
}
