<?php
namespace Digbang\Security;

use Digbang\Security\Configurations\SecurityContextConfiguration;
use Digbang\Security\Contracts\SecurityApi;
use Digbang\Security\Factories\SecurityFactory;
use Digbang\Security\Mappings\CustomTableMapping;
use Digbang\Security\Permissions\PermissionStrategyEventListener;
use Digbang\Security\Urls\PermissionAwareUrlGeneratorExtension;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Routing\UrlGenerator as UrlGeneratorContract;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Http\Request;
use LaravelDoctrine\Fluent\FluentDriver;
use LaravelDoctrine\Fluent\Mapping;
use LaravelDoctrine\ORM\Configuration\MetaData\MetaDataManager;
use LaravelDoctrine\ORM\Extensions\MappingDriverChain;

class SecurityContext
{
	/**
	 * @var Container
	 */
	private $container;

	/**
	 * Configured contexts
	 * @var array
	 */
	private $contexts = [];

	/**
	 * Flyweight Security instances.
	 * @var array
	 */
	private $instances = [];

	/**
	 * Flyweight dependencies.
	 * @var array
	 */
	private $dependencies = [];

	/**
	 * SecurityContext constructor.
	 *
	 * @param Container $container
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	/**
	 * Add a security context.
	 *
	 * @param SecurityContextConfiguration $configuration
	 * @throws \BadMethodCallException
	 */
	public function add(SecurityContextConfiguration $configuration)
	{
		$this->contexts[$configuration->getName()] = $configuration;

		$this->updateMappings($configuration);
	}

	/**
	 * Bind the given security context to the Request and Container.
	 *
	 * @param string  $context
	 * @param Request $request
	 */
	public function bindContext($context, Request $request)
	{
		$security = $this->getSecurity($context);

		$this->container->instance(SecurityApi::class, $security);

	    $this->container->bind(UrlGeneratorContract::class, function() use ($security){
		    return $security->url();
	    });

        $this->container->bind(UrlGenerator::class, function(Container $container) use ($security){
            /** @var PermissionAwareUrlGeneratorExtension $url */
            $url = $container->make(PermissionAwareUrlGeneratorExtension::class);
            $url->setUrlGenerator($security->url());

            return $url;
        });

        $this->container->alias(UrlGenerator::class, 'url');

		$request->setUserResolver(function() use ($security){
            return $security->getUser();
        });
	}

	/**
	 * Get the Security instance for the given context.
	 *
	 * @param string $context
	 * @return Security
	 */
	public function getSecurity($context)
	{
		if (array_key_exists($context, $this->instances))
		{
			return $this->instances[$context];
		}

		$configuration = $this->getConfigurationFor($context);
		$this->addPermissionsFactoryListener($context);

		return $this->instances[$context] = $this->getSecurityFactory()->create($context, $configuration);
	}

	/**
	 * @param $context
	 * @return SecurityContextConfiguration
	 */
	public function getConfigurationFor($context)
	{
		if (! array_key_exists($context, $this->contexts))
		{
			throw new \InvalidArgumentException("Context [$context] is not configured.");
		}

		return $this->contexts[$context];
	}

	/**
	 * @param SecurityContextConfiguration $configuration
	 * @throws \BadMethodCallException
	 */
	private function updateMappings(SecurityContextConfiguration $configuration)
	{
		$mappings = $configuration->getMappings();

		if (! $configuration->isRolesEnabled())
		{
			$this->validateAndCall($mappings['user'], 'disableRoles');
		}

		if (! $configuration->isThrottlesEnabled())
		{
			$this->validateAndCall($mappings['user'], 'disableThrottles');
		}

		if (! $configuration->isPermissionsEnabled())
		{
			$this->validateAndCall($mappings['user'], 'disablePermissions');

			if (isset($mappings['role']))
			{
				$this->validateAndCall($mappings['role'], 'disablePermissions');
			}
		}

		if ($table = $configuration->getTable('usersRoles'))
		{
			$this->validateAndCall($mappings['user'], 'changeRolesJoinTable', $table);

			if (isset($mappings['role']))
			{
				$this->validateAndCall($mappings['role'], 'changeRolesJoinTable', $table);
			}
		}

		$mappingObjects = [];
		foreach ($mappings as $entity => $mapping)
		{
			$entityMapping = $this->makeMapping($mapping);

			if ($entityMapping instanceof CustomTableMapping && $table = $configuration->getTable($entity))
			{
				$entityMapping->setTable($table);
			}

			$mappingObjects[] = $entityMapping;
		}

		$this->addMappings($mappingObjects);
	}

	/**
	 * @param Mapping|string $mapping
	 *
	 * @return Mapping
	 */
	private function makeMapping($mapping)
	{
		if ($mapping instanceof Mapping)
		{
			return $mapping;
		}

		return $this->container->make($mapping);
	}

	/**
	 * @param Mapping|string &$mapping
	 * @param string $method
	 * @param ...$params
	 * @return mixed
	 *
	 * @throws \BadMethodCallException
	 */
	private function validateAndCall(&$mapping, $method, ...$params)
	{
		$mapping = $this->makeMapping($mapping);

		if (! method_exists($mapping, $method))
		{
			throw new \BadMethodCallException("EntityMapping [" . get_class($mapping) .
				"] does not implement '$method'."
			);
		}

		return call_user_func_array([$mapping, $method], $params);
	}

	/**
	 * @param string $context
	 */
	private function addPermissionsFactoryListener($context)
	{
		/** @var SecurityContextConfiguration $configuration */
		$configuration = $this->contexts[$context];

		if ($configuration->isPermissionsEnabled())
		{
			/** @var EntityManagerInterface $entityManager */
			$entityManager = $this->container->make(EntityManagerInterface::class);

			$entityManager->getEventManager()->addEventSubscriber(
				new PermissionStrategyEventListener($configuration->getPermissionsFactory())
			);
		}
	}

	/**
	 * @return SecurityFactory
	 */
	private function getSecurityFactory()
	{
		if (! array_key_exists(SecurityFactory::class, $this->dependencies))
		{
			$this->dependencies[SecurityFactory::class] = $this->container->make(SecurityFactory::class);
		}

		return $this->dependencies[SecurityFactory::class];
	}

	/**
	 * @param Mapping[] $mappings
	 */
	private function addMappings($mappings)
	{
		$fluent = $this->getOrCreateFluentDriver();

		foreach ($mappings as $mapping)
		{
			$fluent->addMapping($mapping);
		}
	}

	/**
	 * @return FluentDriver
	 */
	public function getOrCreateFluentDriver()
	{
		/** @var EntityManagerInterface $entityManager */
		$entityManager = $this->container->make(EntityManagerInterface::class);

		/** @var MappingDriverChain $chain */
		$chain = $entityManager->getConfiguration()->getMetadataDriverImpl();

		$drivers = $chain->getDrivers();

		if (array_key_exists('Digbang\\Security', $drivers))
		{
			return $drivers['Digbang\\Security'];
		}

		/** @var MetaDataManager $metaDataManager */
		$metaDataManager = $this->container->make(MetaDataManager::class);

		/** @var FluentDriver $fluent */
		$fluent = $metaDataManager->driver('fluent', ['mappings' => []]);

		$chain->addDriver($fluent, 'Digbang\\Security');

		return $fluent;
	}
}
