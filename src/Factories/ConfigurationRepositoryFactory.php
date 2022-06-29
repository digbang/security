<?php

namespace Digbang\Security\Factories;

use Cartalyst\Sentinel\Cookies\IlluminateCookie;
use Cartalyst\Sentinel\Sessions\IlluminateSession;
use Digbang\Security\Activations\ActivationRepository;
use Digbang\Security\Configurations\SecurityContextConfiguration;
use Digbang\Security\Permissions\InsecurePermissionRepository;
use Digbang\Security\Persistences\PersistenceRepository;
use Digbang\Security\Reminders\ReminderRepository;
use Digbang\Security\Roles\NullRoleRepository;
use Digbang\Security\Roles\RoleRepository;
use Digbang\Security\SecurityContext;
use Digbang\Security\Throttling\ThrottleRepository;
use Digbang\Security\Users\UserRepository;
use Doctrine\ORM\EntityManager;
use Illuminate\Contracts\Container\Container;
use Illuminate\Cookie\CookieJar;
use Illuminate\Http\Request;
use Illuminate\Session\Store;

class ConfigurationRepositoryFactory implements RepositoryFactory
{
    /**
     * @var RepositoryFactory
     */
    private $defaults;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var SecurityContextConfiguration[]
     */
    private $config = [];

    /**
     * ConfigurationRepositoryFactory constructor.
     *
     * @param  Container  $container
     * @param  RepositoryFactory  $defaults
     */
    public function __construct(Container $container, RepositoryFactory $defaults)
    {
        $this->container = $container;
        $this->defaults = $defaults;
    }

    /**
     * @inheritdoc
     */
    public function createPersistenceRepository($context)
    {
        $persistenceRepository = $this->configuration($context)->getPersistenceRepository();
        if ($persistenceRepository) {
            $entityManager = $this->container->make(EntityManager::class);
            $session = new IlluminateSession($this->container->make(Store::class), "persistence:$context");
            $cookie = new IlluminateCookie(
                $this->container->make(Request::class),
                $this->container->make(CookieJar::class),
                $context
            );

            $repository = $this->container->make($persistenceRepository, [
                'entityManager' => $entityManager,
                'session' => $session,
                'cookie' => $cookie,
            ]);
        } else {
            $repository = $this->defaults->createPersistenceRepository($context);
        }

        $repository->setPersistenceMode($this->configuration($context)->isSinglePersistence() ? 'single' : 'multi');

        return $repository;
    }

    /**
     * @inheritdoc
     */
    public function createUserRepository($context, PersistenceRepository $persistenceRepository, RoleRepository $roleRepository)
    {
        $userRepository = $this->configuration($context)->getUserRepository();
        if ($userRepository) {
            $entityManager = $this->container->make(EntityManager::class);

            return $this->container->make($userRepository, [
                'entityManager' => $entityManager,
                'persistences' => $persistenceRepository,
                'roles' => $roleRepository,
            ]);
        }

        return $this->defaults->createUserRepository($context, $persistenceRepository, $roleRepository);
    }

    /**
     * @inheritdoc
     */
    public function createRoleRepository($context)
    {
        if (! $this->configuration($context)->isRolesEnabled()) {
            return new NullRoleRepository();
        }

        $roleRepository = $this->configuration($context)->getRoleRepository();
        if ($roleRepository) {
            $entityManager = $this->container->make(EntityManager::class);

            return $this->container->make($roleRepository, [
                'entityManager' => $entityManager,
            ]);
        }

        return $this->defaults->createRoleRepository($context);
    }

    /**
     * @inheritdoc
     */
    public function createActivationRepository($context)
    {
        $activationRepository = $this->configuration($context)->getActivationRepository();
        if ($activationRepository) {
            $entityManager = $this->container->make(EntityManager::class);

            $repository = $this->container->make($activationRepository, [
                'entityManager' => $entityManager,
            ]);
        } else {
            $repository = $this->defaults->createActivationRepository($context);
        }

        /* @var ActivationRepository $repository */
        $repository->setExpires($this->configuration($context)->getActivationsExpiration());

        return $repository;
    }

    /**
     * @inheritdoc
     */
    public function createReminderRepository($context, UserRepository $userRepository)
    {
        $reminderRepository = $this->configuration($context)->getReminderRepository();
        if ($reminderRepository) {
            $entityManager = $this->container->make(EntityManager::class);

            $repository = $this->container->make($reminderRepository, [
                'entityManager' => $entityManager,
                'users' => $userRepository,
            ]);
        } else {
            $repository = $this->defaults->createReminderRepository($context, $userRepository);
        }

        /* @var ReminderRepository $repository */
        $repository->setExpires($this->configuration($context)->getRemindersExpiration());

        return $repository;
    }

    /**
     * @inheritdoc
     */
    public function createPermissionRepository($context)
    {
        $enabled = $this->configuration($context)->isPermissionsEnabled();

        if (! $enabled) {
            return new InsecurePermissionRepository();
        }

        $permissionRepository = $this->configuration($context)->getPermissionRepository();
        if ($permissionRepository) {
            return $this->container->make($permissionRepository);
        }

        return $this->defaults->createPermissionRepository($context);
    }

    /**
     * @inheritdoc
     */
    public function createThrottleRepository($context)
    {
        $throttleRepository = $this->configuration($context)->getThrottleRepository();
        if ($throttleRepository) {
            $entityManager = $this->container->make(EntityManager::class);

            $repository = $this->container->make($throttleRepository, [
                'entityManager' => $entityManager,
            ]);
        } else {
            $repository = $this->defaults->createThrottleRepository($context);
        }

        /* @var ThrottleRepository $repository */
        $repository->setGlobalInterval($this->configuration($context)->getGlobalThrottleInterval());
        $repository->setGlobalThresholds($this->configuration($context)->getGlobalThrottleThresholds());
        $repository->setIpInterval($this->configuration($context)->getIpThrottleInterval());
        $repository->setIpThresholds($this->configuration($context)->getIpThrottleThresholds());
        $repository->setUserInterval($this->configuration($context)->getUserThrottleInterval());
        $repository->setUserThresholds($this->configuration($context)->getUserThrottleThresholds());

        return $repository;
    }

    /**
     * @param  string  $context
     * @return SecurityContextConfiguration
     */
    private function configuration($context)
    {
        return $this->config[$context] = $this->container->make(SecurityContext::class)->getConfigurationFor($context);
    }
}
