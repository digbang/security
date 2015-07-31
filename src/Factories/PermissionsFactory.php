<?php namespace Digbang\Security\Factories;

use Cartalyst\Sentinel\Permissions\PermissionsInterface;

final class PermissionsFactory
{
	/**
	 * @type \Closure
	 */
	private $factoryMethod;

	/**
	 * PermissionsFactory constructor.
	 *
	 * @param \Closure $factoryMethod
	 */
	public function __construct(\Closure $factoryMethod)
	{
		$this->factoryMethod = $factoryMethod;
	}

	/**
	 * @param array $permissions
	 * @param array $secondaryPermissions
	 *
	 * @return PermissionsInterface
	 */
	public function create(array $permissions = [], array $secondaryPermissions = [])
	{
		$factory = $this->factoryMethod;

		$permissions = $factory($permissions, $secondaryPermissions);

		if (! $permissions instanceof PermissionsInterface)
		{
			throw new \InvalidArgumentException("Class [" . get_class($permissions) . "] must implement " . PermissionsInterface::class);
		}

		return $permissions;
	}
}
