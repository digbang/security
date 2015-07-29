<?php namespace Digbang\Security;

use Cartalyst\Sentinel\Activations\ActivationRepositoryInterface;
use Cartalyst\Sentinel\Checkpoints\ActivationCheckpoint;
use Cartalyst\Sentinel\Checkpoints\ThrottleCheckpoint;
use Cartalyst\Sentinel\Cookies\IlluminateCookie;
use Cartalyst\Sentinel\Hashing\NativeHasher;
use Cartalyst\Sentinel\Reminders\ReminderRepositoryInterface;
use Cartalyst\Sentinel\Sentinel;
use Cartalyst\Sentinel\Sessions\IlluminateSession;
use Digbang\Security\Configurations\SecurityContextConfiguration;
use Digbang\Security\Contracts\Factories\RepositoryFactory;
use Digbang\Security\Permissions\InsecurePermissionRepository;
use Digbang\Security\Permissions\PermissionRepository;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\ServiceProvider;

class SecurityServiceProvider extends ServiceProvider
{
	/**
	 * Bootstrap the application events.
	 *
	 * @param Repository                    $config
	 * @param ReminderRepositoryInterface   $reminderRepository
	 * @param ActivationRepositoryInterface $activationRepository
	 */
	public function boot(Repository $config, ReminderRepositoryInterface $reminderRepository, ActivationRepositoryInterface $activationRepository)
	{
		$this->garbageCollect($config, $reminderRepository, $activationRepository);

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
		$this->loadTranslationsFrom("$basePath/lang",         'digbang.security');
		$this->loadViewsFrom("$basePath/views",               'digbang.security');

        $this->registerPersistences();
        $this->registerUsers();
        $this->registerRoles();
        $this->registerCheckpoints();
        $this->registerReminders();
        $this->registerSentinel();
        $this->setUserResolver();

		$this->registerConfigurations();
		$this->registerPermissionRepository();
	}

    /**
     * Registers the persistences.
     *
     * @return void
     */
    protected function registerPersistences()
    {
        $this->registerSession();
        $this->registerCookie();

        $this->app->singleton('security.persistence', function (Container $app) {
	        /** @type RepositoryFactory $repositoryFactory */
	        $repositoryFactory = $app->make(RepositoryFactory::class);

	        return $repositoryFactory->createPersistenceRepository($app);
        });
    }

    /**
     * Registers the session.
     *
     * @return void
     */
    protected function registerSession()
    {
        $this->app->singleton('security.session', function ($app) {
            $key = $app['config']->get('digbang.security.session');

            return new IlluminateSession($app['session.store'], $key);
        });
    }

    /**
     * Registers the cookie.
     *
     * @return void
     */
    protected function registerCookie()
    {
        $this->app->singleton('security.cookie', function ($app) {
            $key = $app['config']->get('digbang.security.cookie');

            return new IlluminateCookie($app['request'], $app['cookie'], $key);
        });
    }

    /**
     * Registers the users.
     *
     * @return void
     */
    protected function registerUsers()
    {
        $this->registerHasher();

        $this->app->singleton('security.users', function ($app) {
	        /** @type RepositoryFactory $repositoryFactory */
	        $repositoryFactory = $app->make(RepositoryFactory::class);

	        return $repositoryFactory->createUserRepository($app);
        });
    }

    /**
     * Registers the hahser.
     *
     * @return void
     */
    protected function registerHasher()
    {
        $this->app->singleton('security.hasher', NativeHasher::class);
    }

    /**
     * Registers the roles.
     *
     * @return void
     */
    protected function registerRoles()
    {
        $this->app->singleton('security.roles', function ($app) {
	        /** @type RepositoryFactory $repositoryFactory */
	        $repositoryFactory = $app->make(RepositoryFactory::class);

	        return $repositoryFactory->createRoleRepository($app);
        });
    }

    /**
     * Registers the checkpoints.
     *
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function registerCheckpoints()
    {
        $this->registerActivationCheckpoint();
        $this->registerThrottleCheckpoint();

        $this->app->singleton('security.checkpoints', function (Container $app) {
            $activeCheckpoints = $app['config']->get('digbang.security.checkpoints');

            $checkpoints = [];

            foreach ($activeCheckpoints as $checkpoint) {
                if (! isset($app["security.checkpoint.{$checkpoint}"])) {
                    throw new \InvalidArgumentException("Invalid checkpoint [$checkpoint] given.");
                }

                $checkpoints[$checkpoint] = $app["security.checkpoint.{$checkpoint}"];
            }

            return $checkpoints;
        });
    }

    /**
     * Registers the activation checkpoint.
     *
     * @return void
     */
    protected function registerActivationCheckpoint()
    {
        $this->registerActivations();

        $this->app->singleton('security.checkpoint.activation', function ($app) {
            return new ActivationCheckpoint($app['security.activations']);
        });
    }

    /**
     * Registers the activations.
     *
     * @return void
     */
    protected function registerActivations()
    {
        $this->app->singleton('security.activations', function (Container $app) {
	        /** @type RepositoryFactory $repositoryFactory */
	        $repositoryFactory = $app->make(RepositoryFactory::class);

	        return $repositoryFactory->createActivationRepository($app);
        });
    }

    /**
     * Registers the throttle checkpoint.
     *
     * @return void
     */
    protected function registerThrottleCheckpoint()
    {
        $this->registerThrottling();

        $this->app->singleton('security.checkpoint.throttle', function ($app) {
            return new ThrottleCheckpoint(
                $app['security.throttling'],
                $app['request']->getClientIp()
            );
        });
    }

    /**
     * Registers the throttle.
     *
     * @return void
     */
    protected function registerThrottling()
    {
        $this->app->singleton('security.throttling', function (Container $app) {
	        /** @type RepositoryFactory $repositoryFactory */
	        $repositoryFactory = $app->make(RepositoryFactory::class);

	        return $repositoryFactory->createThrottleRepository($app);
        });
    }

    /**
     * Registers the reminders.
     *
     * @return void
     */
    protected function registerReminders()
    {
        $this->app->singleton('security.reminders', function (Container $app) {
	        /** @type RepositoryFactory $repositoryFactory */
	        $repositoryFactory = $app->make(RepositoryFactory::class);

	        return $repositoryFactory->createReminderRepository($app);
        });
    }

    /**
     * Registers sentinel.
     *
     * @return void
     */
    protected function registerSentinel()
    {
        $this->app->bind([Sentinel::class => 'sentinel'], function ($app) {
            $sentinel = new Sentinel(
                $app['security.persistence'],
                $app['security.users'],
                $app['security.roles'],
                $app['security.activations'],
                $app['events']
            );

            if (isset($app['security.checkpoints'])) {
                foreach ($app['security.checkpoints'] as $key => $checkpoint) {
                    $sentinel->addCheckpoint($key, $checkpoint);
                }
            }

            $sentinel->setActivationRepository($app['security.activations']);
            $sentinel->setReminderRepository($app['security.reminders']);

            $sentinel->setRequestCredentials(function () use ($app) {
	            /** @type Request $request */
                $request = $app['request'];

                $login = $request->getUser();
                $password = $request->getPassword();

                if ($login === null && $password === null) {
                    return;
                }

                return compact('login', 'password');
            });

            $sentinel->creatingBasicResponse(function () {
                $headers = ['WWW-Authenticate' => 'Basic'];

                return new Response('Invalid credentials.', 401, $headers);
            });

            return $sentinel;
        });
    }

    /**
     * {@inheritDoc}
     */
    public function provides()
    {
        return [
            'security.session',
            'security.cookie',
            'security.persistence',
            'security.hasher',
            'security.users',
            'security.roles',
            'security.activations',
            'security.checkpoint.activation',
            'security.throttling',
            'security.checkpoint.throttle',
            'security.checkpoints',
            'security.reminders',
            'security',
        ];
    }

    /**
     * Sets the user resolver on the request class.
     *
     * @return void
     */
    protected function setUserResolver()
    {
	    /** @type Request $request */
        $request = $this->app['request'];

	    $request->setUserResolver(function () {
            return $this->app['sentinel']->getUser();
        });
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

	/**
	 * Garbage collect activations and reminders.
	 *
	 * @param ReminderRepositoryInterface   $reminderRepository
	 * @param ActivationRepositoryInterface $activationRepository
	 */
    protected function garbageCollect(SecurityContext $securityContext, ReminderRepositoryInterface $reminderRepository, ActivationRepositoryInterface $activationRepository)
    {
        foreach ($securityContext->getConfigurations() as $configuration)
        {
            /** @type SecurityContextConfiguration $configuration */
            $this->sweep($activationRepository, $configuration->getActivationsLottery());
            $this->sweep($reminderRepository,   $configuration->getRemindersLottery());
        }
    }

	/**
     * Determine if the configuration odds hit the lottery.
     *
     * @param  array  $lottery
     * @return bool
     */
    protected function configHitsLottery(array $lottery)
    {
        return mt_rand(1, $lottery[1]) <= $lottery[0];
    }

	/**
     * Sweep expired codes.
     *
     * @param  ReminderRepositoryInterface|ActivationRepositoryInterface $repository
     * @param  array  $lottery
     * @return void
     */
    protected function sweep($repository, $lottery)
    {
        if ($this->configHitsLottery($lottery))
        {
            try
            {
                $repository->removeExpired();
            }
            catch (\Exception $e)
            {
	            // Do nothing
            }
        }
    }
}
