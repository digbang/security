<?php namespace Digbang\Security;

use Digbang\Doctrine\Metadata\DecoupledMappingDriver;
use Digbang\Doctrine\Metadata\EntityMapping;
use Digbang\Security\Configurations\SecurityContextConfiguration;
use Digbang\Security\Contracts\SecurityApi;
use Digbang\Security\Factories\SecurityFactory;
use Digbang\Security\Mappings\CustomTableMapping;
use Digbang\Security\Permissions\PermissionStrategyEventListener;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Http\Request;

final class SecurityContext
{
	/**
	 * @type SecurityFactory
	 */
	private $securityFactory;

	/**
	 * @type DecoupledMappingDriver
	 */
	private $mappingDriver;

	/**
	 * Configured contexts
	 * @type array
	 */
	private $contexts = [];

	/**
	 * @type Container
	 */
	private $container;

	/**
	 * Flyweight Security instances.
	 * @type array
	 */
	private $instances = [];

	/**
	 * SecurityContext constructor.
	 *
	 * @param SecurityFactory        $securityFactory
	 * @param DecoupledMappingDriver $mappingDriver
	 * @param Container              $container
	 */
	public function __construct(SecurityFactory $securityFactory, DecoupledMappingDriver $mappingDriver, Container $container)
	{
		$this->securityFactory = $securityFactory;
		$this->mappingDriver   = $mappingDriver;
		$this->container       = $container;
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
		$this->container->bind(SecurityApi::class, function() use ($context){
		    return $this->getSecurity($context);
	    });

	    $this->container->bind(UrlGenerator::class, function() use ($context){
		    return $this->getSecurity($context)->url();
	    });

		$request->setUserResolver(function() use ($context){
            return $this->getSecurity($context)->getUser();
        });

		$this->addPermissionsFactoryListener($context);
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

		return $this->instances[$context] = $this->securityFactory->create($context, $configuration);
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

		foreach ($mappings as $entity => $mapping)
		{
			$entityMapping = $this->makeMapping($mapping);

			if ($entityMapping instanceof CustomTableMapping && $table = $configuration->getTable($entity))
			{
				$entityMapping->setTable($table);
			}

			$this->mappingDriver->addMapping($entityMapping);
		}
	}

	private function makeMapping($mapping)
	{
		if ($mapping instanceof EntityMapping)
		{
			return $mapping;
		}

		return $this->container->make($mapping);
	}

	/**
	 * @param EntityMapping|string &$mapping
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
		/** @type SecurityContextConfiguration $configuration */
		$configuration = $this->contexts[$context];

		if ($configuration->isPermissionsEnabled())
		{
			/** @type EntityManagerInterface $entityManager */
			$entityManager = $this->container->make(EntityManagerInterface::class);

			$entityManager->getEventManager()->addEventSubscriber(
				new PermissionStrategyEventListener($configuration->getPermissionsFactory())
			);
		}
	}
}
