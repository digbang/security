<?php namespace Digbang\Security\Factories;

use Digbang\Security\Activations\ActivationRepository;
use Digbang\Security\Permissions\PermissionRepository;
use Digbang\Security\Persistences\PersistenceRepository;
use Digbang\Security\Reminders\ReminderRepository;
use Digbang\Security\Roles\RoleRepository;
use Digbang\Security\Throttling\ThrottleRepository;
use Digbang\Security\Users\UserRepository;

interface RepositoryFactory
{
	/**
	 * @param string $context
	 * @param bool   $single
	 * @return PersistenceRepository
	 */
	public function createPersistenceRepository($context, $single = false);

	/**
	 * @param PersistenceRepository $persistenceRepository
	 * @param \Closure|null         $permissionsFactory
	 *
	 * @return UserRepository
	 */
	public function createUserRepository(PersistenceRepository $persistenceRepository, \Closure $permissionsFactory = null);

	/**
	 * @return RoleRepository
	 */
	public function createRoleRepository();

	/**
	 * @param int $expires
	 * @return ActivationRepository
	 */
	public function createActivationRepository($expires);

	/**
	 * @param UserRepository $userRepository
	 * @param int            $expires
	 *
	 * @return ReminderRepository
	 */
	public function createReminderRepository(UserRepository $userRepository, $expires);

	/**
	 * @param bool $enabled
	 *
	 * @return PermissionRepository
	 */
	public function createPermissionRepository($enabled = true);

	/**
	 * @param int       $globalInterval
	 * @param int|array $globalThresholds
	 * @param int       $ipInterval
	 * @param int|array $ipThresholds
	 * @param int       $userInterval
	 * @param int|array $userThresholds
	 *
	 * @return ThrottleRepository
	 */
	public function createThrottleRepository($globalInterval, $globalThresholds, $ipInterval, $ipThresholds, $userInterval, $userThresholds);
}
