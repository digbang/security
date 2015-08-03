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
	 * @param \Closure                       $permissionsFactory
	 *
	 * @return UserRepositoryInterface
	 */
	public function createUserRepository(PersistenceRepositoryInterface $persistenceRepository, \Closure $permissionsFactory = null);

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
	 * @param UserRepositoryInterface $userRepository
	 * @param int                     $expires
	 *
	 * @return ReminderRepositoryInterface
	 */
	public function createReminderRepository(UserRepositoryInterface $userRepository, $expires);

	/**
	 * @return PermissionRepository
	 */
	public function createPermissionRepository();

	/**
	 * @param int $globalInterval
	 * @param int|array $globalThresholds
	 * @param int $ipInterval
	 * @param int|array $ipThresholds
	 * @param int $userInterval
	 * @param int|array $userThresholds
	 *
	 * @return ThrottleRepositoryInterface
	 */
	public function createThrottleRepository(
		$globalInterval,
		$globalThresholds,
		$ipInterval,
		$ipThresholds,
		$userInterval,
		$userThresholds
	);
}
