<?php namespace Digbang\Security\Contracts;

use Cartalyst\Sentinel\Activations\ActivationRepositoryInterface;
use Cartalyst\Sentinel\Checkpoints\CheckpointInterface;
use Cartalyst\Sentinel\Persistences\PersistenceRepositoryInterface;
use Cartalyst\Sentinel\Reminders\ReminderRepositoryInterface;
use Cartalyst\Sentinel\Roles\RoleRepositoryInterface;
use Cartalyst\Sentinel\Users\UserRepositoryInterface;
use Closure;
use Digbang\Security\Contracts\Entities\User;

interface SecurityApi
{
	/**
	 * Registers a user. You may provide a callback to occur before the user
	 * is saved, or provide a true boolean as a shortcut to activation.
	 *
	 * @param  array         $credentials
	 * @param  \Closure|bool $callback
	 *
	 * @return User|bool
	 * @throws \InvalidArgumentException
	 */
	public function register(array $credentials, $callback = null);

	/**
	 * Registers and activates the user.
	 *
	 * @param  array $credentials
	 *
	 * @return User|bool
	 */
	public function registerAndActivate(array $credentials);

	/**
	 * Activates the given user.
	 *
	 * @param  mixed $user
	 *
	 * @return bool
	 * @throws \InvalidArgumentException
	 */
	public function activate($user);

	/**
	 * Checks to see if a user is logged in.
	 *
	 * @return User|bool
	 */
	public function check();

	/**
	 * Checks to see if a user is logged in, bypassing checkpoints
	 *
	 * @return User|bool
	 */
	public function forceCheck();

	/**
	 * Checks if we are currently a guest.
	 *
	 * @return User|bool
	 */
	public function guest();

	/**
	 * Authenticates a user, with "remember" flag.
	 *
	 * @param  User|array $credentials
	 * @param  bool       $remember
	 * @param  bool       $login
	 *
	 * @return User|bool
	 */
	public function authenticate($credentials, $remember = false, $login = true);

	/**
	 * Authenticates a user, with the "remember" flag.
	 *
	 * @param  User|array $credentials
	 *
	 * @return User|bool
	 */
	public function authenticateAndRemember($credentials);

	/**
	 * Forces an authentication to bypass checkpoints.
	 *
	 * @param  User|array $credentials
	 * @param  bool       $remember
	 *
	 * @return User|bool
	 */
	public function forceAuthenticate($credentials, $remember = false);

	/**
	 * Forces an authentication to bypass checkpoints, with the "remember" flag.
	 *
	 * @param  User|array $credentials
	 *
	 * @return User|bool
	 */
	public function forceAuthenticateAndRemember($credentials);

	/**
	 * Attempt a stateless authentication.
	 *
	 * @param  User|array $credentials
	 *
	 * @return User|bool
	 */
	public function stateless($credentials);

	/**
	 * Attempt to authenticate using HTTP Basic Auth.
	 *
	 * @return mixed
	 */
	public function basic();

	/**
	 * Returns the request credentials.
	 *
	 * @return array
	 */
	public function getRequestCredentials();

	/**
	 * Sets the closure which resolves the request credentials.
	 *
	 * @param  \Closure $requestCredentials
	 *
	 * @return void
	 */
	public function setRequestCredentials(Closure $requestCredentials);

	/**
	 * Sends a response when HTTP basic authentication fails.
	 *
	 * @return mixed
	 * @throws \RuntimeException
	 */
	public function getBasicResponse();

	/**
	 * Sets the callback which creates a basic response.
	 *
	 * @param Closure $basicResponse
	 * @return void
	 */
	public function creatingBasicResponse(Closure $basicResponse);

	/**
	 * Persists a login for the given user.
	 *
	 * @param  User $user
	 * @param  bool $remember
	 *
	 * @return User|bool
	 */
	public function login(User $user, $remember = false);

	/**
	 * Persists a login for the given user, with the "remember" flag.
	 *
	 * @param  User $user
	 * @return User|bool
	 */
	public function loginAndRemember(User $user);

	/**
	 * Logs the current user out.
	 *
	 * @param  User $user
	 * @param  bool $everywhere
	 *
	 * @return bool
	 */
	public function logout(User $user = null, $everywhere = false);

	/**
	 * Pass a closure to Sentinel to bypass checkpoints.
	 *
	 * @param  Closure $callback
	 * @param  array   $checkpoints
	 *
	 * @return mixed
	 */
	public function bypassCheckpoints(Closure $callback, $checkpoints = []);

	/**
	 * Checks if checkpoints are enabled.
	 *
	 * @return bool
	 */
	public function checkpointsStatus();

	/**
	 * Enables checkpoints.
	 *
	 * @return void
	 */
	public function enableCheckpoints();

	/**
	 * Disables checkpoints.
	 *
	 * @return void
	 */
	public function disableCheckpoints();

	/**
	 * Add a new checkpoint to Sentinel.
	 *
	 * @param  string              $key
	 * @param  CheckpointInterface $checkpoint
	 *
	 * @return void
	 */
	public function addCheckpoint($key, CheckpointInterface $checkpoint);

	/**
	 * Removes a checkpoint.
	 *
	 * @param  string $key
	 *
	 * @return void
	 */
	public function removeCheckpoint($key);

	/**
	 * Returns the currently logged in user, lazily checking for it.
	 *
	 * @param  bool $check
	 *
	 * @return User
	 */
	public function getUser($check = true);

	/**
	 * Sets the user associated with Sentinel (does not log in).
	 *
	 * @param User $user
	 *
	 * @return void
	 */
	public function setUser(User $user);

	/**
	 * Returns the user repository.
	 *
	 * @return UserRepositoryInterface
	 */
	public function getUserRepository();

	/**
	 * Returns the role repository.
	 *
	 * @return RoleRepositoryInterface
	 */
	public function getRoleRepository();

	/**
	 * Returns the persistences repository.
	 *
	 * @return PersistenceRepositoryInterface
	 */
	public function getPersistenceRepository();

	/**
	 * Returns the activations repository.
	 *
	 * @return ActivationRepositoryInterface
	 */
	public function getActivationRepository();

	/**
	 * Returns the reminders repository.
	 *
	 * @return ReminderRepositoryInterface
	 */
	public function getReminderRepository();
}