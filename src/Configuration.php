<?php namespace Digbang\Security;

use Digbang\Doctrine\Metadata\DecoupledMappingDriver;
use Illuminate\Contracts\Container\Container;

/**
 * Class Configuration
 *
 * @package Digbang\Security
 *
 * @property-read Contracts\SingleMappingConfiguration   $activations
 * @property-read Contracts\SingleMappingConfiguration   $persistences
 * @property-read Contracts\SingleMappingConfiguration   $reminders
 * @property-read Contracts\SingleMappingConfiguration   $roles
 * @property-read Contracts\SingleMappingConfiguration   $throttle
 * @property-read Contracts\SingleMappingConfiguration   $globalThrottle
 * @property-read Contracts\SingleMappingConfiguration   $ipThrottle
 * @property-read Contracts\SingleMappingConfiguration   $userThrottle
 * @property-read Contracts\SingleMappingConfiguration   $users
 * @property-read Configurations\EmbeddableConfiguration $permissions
 */
final class Configuration
{
	private $configurations = [];

	/**
	 * @type Container
	 */
	private $container;

	/**
	 * @param Container                              $container
	 * @param Contracts\SingleMappingConfiguration   $activations
	 * @param Contracts\SingleMappingConfiguration   $peristences
	 * @param Contracts\SingleMappingConfiguration   $reminders
	 * @param Contracts\SingleMappingConfiguration   $roles
	 * @param Contracts\MultiMappingConfiguration    $throttles
	 * @param Contracts\SingleMappingConfiguration   $throttle
	 * @param Contracts\SingleMappingConfiguration   $globalThrottle
	 * @param Contracts\SingleMappingConfiguration   $ipThrottle
	 * @param Contracts\SingleMappingConfiguration   $userThrottle
	 * @param Contracts\SingleMappingConfiguration   $users
	 * @param Configurations\EmbeddableConfiguration $permissions
	 */
	public function __construct(
		Container $container,
		Contracts\SingleMappingConfiguration   $activations,
		Contracts\SingleMappingConfiguration   $peristences,
		Contracts\SingleMappingConfiguration   $reminders,
		Contracts\SingleMappingConfiguration   $roles,
		Contracts\MultiMappingConfiguration    $throttles,
		Contracts\SingleMappingConfiguration   $throttle,
		Contracts\SingleMappingConfiguration   $globalThrottle,
		Contracts\SingleMappingConfiguration   $ipThrottle,
		Contracts\SingleMappingConfiguration   $userThrottle,
		Contracts\SingleMappingConfiguration   $users,
		Configurations\EmbeddableConfiguration $permissions
	){
		$this->container = $container;

		$this->configurations = [
			'activations' => $activations,
			'permissions' => $permissions,
			'peristences' => $peristences,
			'reminders'   => $reminders,
			'roles'       => $roles,
			'throttles'   => $throttles,
			'users'       => $users,
		];

		$throttles->add('throttle',       $throttle);
		$throttles->add('globalThrottle', $globalThrottle);
		$throttles->add('ipThrottle',     $ipThrottle);
		$throttles->add('userThrottle',   $userThrottle);

		$this->applyDefaults();
	}

	/**
	 * @param string $prop
	 * @return Contracts\Configuration|Contracts\SingleMappingConfiguration|Contracts\MultiMappingConfiguration
	 * @throws \BadMethodCallException
	 */
	public function __get($prop)
	{
		if (array_key_exists($prop, $this->configurations))
		{
			return $this->configurations[$prop];
		}
		elseif (in_array($prop, ['throttle', 'globalThrottle', 'ipThrottle', 'userThrottle']))
		{
			return $this->configurations['throttles']->get($prop);
		}

		throw new \BadMethodCallException("Property $prop does not exist.");
	}

	/**
	 * Read the configuration file and add mappings to the decoupled mapping driver.
	 *
	 * @param DecoupledMappingDriver $mappingDriver
	 */
	public function addMappings(DecoupledMappingDriver $mappingDriver)
	{
		foreach ($this->configurations as $configuration)
		{
			/** @type Contracts\Configuration $configuration */
			$configuration->map($mappingDriver);
		}
	}

	private function applyDefaults()
	{
		$this->activations->setMapping(new Mappings\ActivationMapping);
		$this->permissions->setMapping(new Mappings\PermissionCollectionMapping);
		$this->persistences->setMapping(new Mappings\PersistenceMapping);
		$this->reminders->setMapping(new Mappings\ReminderMapping);
		$this->roles->setMapping(new Mappings\RoleMapping);
		$this->throttle->setMapping(new Mappings\ThrottleMapping);
		$this->globalThrottle->setMapping(new Mappings\GlobalThrottleMapping);
		$this->ipThrottle->setMapping(new Mappings\IpThrottleMapping);
		$this->userThrottle->setMapping(new Mappings\UserThrottleMapping);
		$this->users->setMapping(new Mappings\UserMapping);
	}
}
