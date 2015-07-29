<?php namespace Digbang\Security\Contracts\Factories;

use Cartalyst\Sentinel\Activations\ActivationRepositoryInterface;
use Cartalyst\Sentinel\Persistences\PersistenceRepositoryInterface;
use Cartalyst\Sentinel\Reminders\ReminderRepositoryInterface;
use Cartalyst\Sentinel\Roles\RoleRepositoryInterface;
use Cartalyst\Sentinel\Throttling\ThrottleRepositoryInterface;
use Cartalyst\Sentinel\Users\UserRepositoryInterface;
use Digbang\Security\Permissions\PermissionRepository;

interface RepositoryFactory
{
	/**
	 * @param string $context
	 * @param bool   $single
	 * @return PersistenceRepositoryInterface
	 */
	public function createPersistenceRepository($context, $single = false);

	/**
	 * @param PersistenceRepositoryInterface $persistenceRepository
	 * @return UserRepositoryInterface
	 */
	public function createUserRepository(PersistenceRepositoryInterface $persistenceRepository);

	/**
	 * @return RoleRepositoryInterface
	 */
	public function createRoleRepository();

	/**
	 * @param int $expires
	 * @return ActivationRepositoryInterface
	 */
	public function createActivationRepository($expires);

	/**
	 * @param int $expires
	 * @return ReminderRepositoryInterface
	 */
	public function createReminderRepository($expires);

	/**
	 * @return PermissionRepository
	 */
	public function createPermissionRepository();

	/**
	 * @return ThrottleRepositoryInterface
	 */
	public function createThrottleRepository();
}
