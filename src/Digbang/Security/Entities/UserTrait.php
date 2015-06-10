<?php namespace Digbang\Security\Entities;

use Carbon\Carbon;
use Cartalyst\Sentry\Groups\GroupInterface;
use Cartalyst\Sentry\Users as Exceptions;
use Digbang\Doctrine\TimestampsTrait;
use Digbang\Security\Contracts\Permission as PermissionInterface;
use Doctrine\Common\Collections\ArrayCollection;

trait UserTrait
{
	use TimestampsTrait;

	/**
	 * @type int
	 */
	private $id;

	/**
	 * @type string
	 */
	private $email;

	/**
	 * @type string
	 */
	private $password;

	/**
	 * @type string
	 */
	private $firstName;

	/**
	 * @type string
	 */
	private $lastName;

	/**
	 * @type \DateTimeInterface
	 */
	private $lastLogin;

	/**
	 * @type bool
	 */
	private $activated = false;

	/**
	 * @type string
	 */
	private $activationCode;

	/**
	 * @type \DateTimeInterface
	 */
	private $activatedAt;

	/**
	 * @type bool
	 */
	private $superUser = false;

	/**
	 * @type string
	 */
	private $persistCode;

	/**
	 * @type string
	 */
	private $resetPasswordCode;

	/**
	 * @type ArrayCollection
	 */
	private $groups;

	/**
	 * @type ArrayCollection
	 */
	private $permissions;

	/**
	 * @type array
	 */
	private $mergedPermissions;

	/**
	 * This is needed to emulate an AR behavior.
	 * Sentry uses AR to save/delete entities...
	 *
	 * @type \Digbang\Security\Repositories\DoctrineUserRepository|\Doctrine\Common\Persistence\ObjectRepository
	 */
	private $userRepository;

	/**
	 * @param \Digbang\Security\Repositories\DoctrineUserRepository|\Doctrine\Common\Persistence\ObjectRepository $userRepository
	 */
	public function setRepository(\Doctrine\Common\Persistence\ObjectRepository $userRepository)
	{
		$this->userRepository = $userRepository;
	}

	/**
	 * Returns the user's ID.
	 *
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Returns the name for the user's login.
	 *
	 * @return string
	 */
	public function getLoginName()
	{
		return 'email';
	}

	/**
	 * Returns the user's login.
	 *
	 * @return string
	 */
	public function getLogin()
	{
		return $this->email;
	}

	/**
	 * Returns the name for the user's password.
	 *
	 * @return string
	 */
	public function getPasswordName()
	{
		return 'password';
	}

	/**
	 * Returns the user's password (hashed).
	 *
	 * @return string
	 */
	public function getPassword()
	{
		return $this->password;
	}

	/**
	 * Returns permissions for the user.
	 *
	 * @return array
	 */
	public function getPermissions()
	{
		return $this->permissions->toArray();
	}

	/**
	 * Check if the user is activated.
	 *
	 * @return bool
	 */
	public function isActivated()
	{
		return $this->activated;
	}

	/**
	 * Checks if the user is a super user - has
	 * access to everything regardless of permissions.
	 *
	 * @return bool
	 */
	public function isSuperUser()
	{
		return $this->superUser;
	}

	/**
	 * Validates the user && throws a number of
	 * Exceptions if validation fails.
	 *
	 * @return bool
	 * @throws \Cartalyst\Sentry\Users\LoginRequiredException
	 * @throws \Cartalyst\Sentry\Users\UserExistsException
	 */
	public function validate()
	{
		if (! $this->email)
		{
			throw new Exceptions\LoginRequiredException("A login is required for a user, none given.");
		}

		if (! $this->getPassword())
		{
			throw new Exceptions\PasswordRequiredException("A password is required for user [$this->email], none given.");
		}

		// Check if the user already exists
		$persistedUser = $this->userRepository->findOneBy(['email' => $this->email]);

		if ($persistedUser && $persistedUser->getId() != $this->getId())
		{
			throw new Exceptions\UserExistsException(
				"A user already exists with login [$this->email], logins must be unique for users."
			);
		}

		return true;
	}

	/**
	 * Save the user.
	 *
	 * @return bool
	 */
	public function save()
	{
		$this->userRepository->save($this);

		return true;
	}

	/**
	 * Delete the user.
	 *
	 * @return bool
	 */
	public function delete()
	{
		$this->userRepository->delete($this);

		return true;
	}

	/**
	 * Gets a code for when the user is
	 * persisted to a cookie or session which
	 * identifies the user.
	 *
	 * @return string
	 */
	public function getPersistCode()
	{
		return $this->persistCode;
	}

	/**
	 * Checks the given persist code.
	 *
	 * @param  string $persistCode
	 *
	 * @return bool
	 */
	public function checkPersistCode($persistCode)
	{
		return $this->persistCode == $persistCode;
	}

	/**
	 * Get an activation code for the given user.
	 *
	 * @return string
	 */
	public function getActivationCode()
	{
		$this->activationCode = $activationCode = uniqid('', true);

		$this->save();

		return $activationCode;
	}

	/**
	 * Attempts to activate the given user by checking
	 * the activate code. If the user is activated already,
	 * an Exception is thrown.
	 *
	 * @param  string $activationCode
	 *
	 * @return bool
	 * @throws \Cartalyst\Sentry\Users\UserAlreadyActivatedException
	 */
	public function attemptActivation($activationCode)
	{
		if ($this->isActivated())
		{
			throw new Exceptions\UserAlreadyActivatedException;
		}

		if ($this->activationCode != $activationCode)
		{
			return false;
		}

		$this->forceActivation();

		return $this->save();
	}

	/**
	 * Checks the password passed matches the user's password.
	 *
	 * @param  string $password
	 *
	 * @return bool
	 */
	public function checkPassword($password)
	{
		return $this->userRepository->checkHash($this->getPassword(), $password);
	}

	/**
	 * Get a reset password code for the given user.
	 *
	 * @return string
	 */
	public function getResetPasswordCode()
	{
		$this->resetPasswordCode = $resetCode = uniqid('', true);

		$this->save();

		return $resetCode;
	}

	/**
	 * Checks if the provided user reset password code is
	 * valid without actually resetting the password.
	 *
	 * @param  string $resetCode
	 *
	 * @return bool
	 */
	public function checkResetPasswordCode($resetCode)
	{
		return $this->resetPasswordCode == $resetCode;
	}

	/**
	 * Attempts to reset a user's password by matching
	 * the reset code generated with the user's.
	 *
	 * @param  string $resetCode
	 * @param  string $newPassword
	 *
	 * @return bool
	 */
	public function attemptResetPassword($resetCode, $newPassword)
	{
		if ($this->checkResetPasswordCode($resetCode))
		{
			$this->password          = $this->userRepository->hash($newPassword);
			$this->resetPasswordCode = null;

			return $this->save();
		}

		return false;
	}

	/**
	 * Wipes out the data associated with resetting
	 * a password.
	 *
	 * @return void
	 */
	public function clearResetPassword()
	{
		if ($this->resetPasswordCode)
		{
			$this->resetPasswordCode = null;
			$this->save();
		}
	}

	/**
	 * Returns an array of groups which the given
	 * user belongs to.
	 *
	 * @return array
	 */
	public function getGroups()
	{
		return $this->groups->toArray();
	}

	/**
	 * Adds the user to the given group
	 *
	 * @param  \Cartalyst\Sentry\Groups\GroupInterface $group
	 *
	 * @return bool
	 */
	public function addGroup(GroupInterface $group)
	{
		return $this->groups->add($group);
	}

	/**
	 * Removes the user from the given group.
	 *
	 * @param  \Cartalyst\Sentry\Groups\GroupInterface $group
	 *
	 * @return bool
	 */
	public function removeGroup(GroupInterface $group)
	{
		return $this->groups->removeElement($group);
	}

	/**
	 * See if the user is in the given group.
	 *
	 * @param  \Cartalyst\Sentry\Groups\GroupInterface $group
	 *
	 * @return bool
	 */
	public function inGroup(GroupInterface $group)
	{
		return $this->groups->contains($group);
	}

	/**
	 * Returns an array of valid permissions for the user.
	 *
	 * @return ArrayCollection
	 */
	public function getMergedPermissions()
	{
		if (! $this->mergedPermissions)
		{
			$permissions = [];

			foreach ($this->getGroups() as $group)
			{
				/** @type GroupInterface $group */
				foreach ($group->getPermissions() as $permission)
				{
					$permissions[(string) $permission] = $permission;
				}
			}

			foreach ($this->permissions as $permission)
			{
				/** @type UserPermission $permission */
				$key = (string) $permission;

				if ($permission->isAllowed())
				{
					$permissions[$key] = $permission;
				}
				elseif (array_key_exists($key, $permissions))
				{
					unset($permissions[$key]);
				}
			}

			$this->mergedPermissions = new ArrayCollection(array_values($permissions));
		}

		return $this->mergedPermissions;
	}

	/**
	 * See if a user has access to the passed permission(s).
	 * Permissions are merged from all groups the user belongs to
	 * && then are checked against the passed permission(s).
	 *
	 * If multiple permissions are passed, the user must
	 * have access to all permissions passed through, unless the
	 * "all" flag is set to false.
	 *
	 * Super users have access no matter what.
	 *
	 * @param  string|array $permissions
	 * @param  bool         $all
	 *
	 * @return bool
	 */
	public function hasAccess($permissions, $all = true)
	{
		if ($this->isSuperUser())
		{
			return true;
		}

		return $this->hasPermission($permissions, $all);
	}

	/**
	 * See if a user has access to the passed permission(s).
	 * Permissions are merged from all groups the user belongs to
	 * && then are checked against the passed permission(s).
	 *
	 * If multiple permissions are passed, the user must
	 * have access to all permissions passed through, unless the
	 * "all" flag is set to false.
	 *
	 * Super users DON'T have access no matter what.
	 *
	 * @param  string|array $permissions
	 * @param  bool         $all
	 *
	 * @return bool
	 */
	public function hasPermission($permissions, $all = true)
	{
		return array_reduce((array) $permissions, function($carry, $permission) use ($all) {
			if ($all) {
				return $carry && $this->hasSinglePermission($permission);
			}

			return $carry || $this->hasSinglePermission($permission);
		}, $all);
	}

	protected function hasSinglePermission($aPermission)
	{
		return $this
			->getMergedPermissions()
			->exists(function($key, PermissionInterface $permission) use ($aPermission) {
				return $permission->allows($aPermission);
			});
	}

	/**
	 * Returns if the user has access to any of the
	 * given permissions.
	 *
	 * @param  array $permissions
	 *
	 * @return bool
	 */
	public function hasAnyAccess(array $permissions)
	{
		return $this->hasAccess($permissions, false);
	}

	/**
	 * Records a login for the user.
	 *
	 * @return void
	 */
	public function recordLogin()
	{
		$this->lastLogin = new Carbon;

		$this->save();
	}

	/**
	 * @param string $firstName
	 * @param string $lastName
	 */
	public function setName($firstName, $lastName)
	{
		$this->firstName = $firstName;
		$this->lastName  = $lastName;
	}

	/**
	 * @return string
	 */
	public function getFirstName()
	{
		return $this->firstName;
	}

	/**
	 * @return string
	 */
	public function getLastName()
	{
		return $this->lastName;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->firstName . ' ' . $this->lastName;
	}

	/**
	 * @return string
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * @return \DateTimeInterface
	 */
	public function getLastLogin()
	{
		return $this->lastLogin;
	}

	/**
	 * @return \DateTimeInterface
	 */
	public function getActivatedAt()
	{
		return $this->activatedAt;
	}

	/**
	 * @param string $newPassword (hashed)
	 */
	public function changePassword($newPassword)
	{
		$this->password = $newPassword;
	}

	/**
	 * @return \Carbon\Carbon
	 */
	public function getCreatedAt()
	{
		return $this->createdAt;
	}

	/**
	 * @return \Carbon\Carbon
	 */
	public function getUpdatedAt()
	{
		return $this->updatedAt;
	}

	/**
	 * @param string $firstName
	 * @param string $lastName
	 *
	 * @return void
	 */
	public function named($firstName, $lastName)
	{
		$this->firstName = $firstName;
		$this->lastName  = $lastName;
	}

	/**
	 * @return void
	 */
	public function forceActivation()
	{
		$this->activated      = true;
		$this->activatedAt    = new Carbon;
		$this->activationCode = null;
	}

	/**
	 * @return void
	 */
	public function deactivate()
	{
		$this->activated      = false;
		$this->activatedAt    = null;
		$this->activationCode = null;
	}

	/**
	 * @param $permissions
	 *
	 * @return void
	 */
	public function setAllPermissions($permissions)
	{
		// Denials: Group permissions missing from the given permissions array
		$denials = [];

		foreach ($this->getGroupPermissions() as $groupPermission)
		{
			$key = array_search((string) $groupPermission, $permissions);

			if ($key !== false)
			{
				// Remove all permissions already granted by group
				unset($permissions[$key]);
			}
			else
			{
				// Deny missing group permission
				$denials[] = (string) $groupPermission;
			}
		}

		foreach ($this->permissions as $userPermission)
		{
			$key = array_search((string) $userPermission, $permissions);

			/** @type UserPermission $userPermission */
			if ($key === false)
			{
				if (($key = array_search($userPermission, $denials)) !== false)
				{
					/**
					 * Edge case:
					 * 1. first it was given to the user
					 * 2. then it was given to the group
					 * 3. finally, it was denied to the user
					 */
					$userPermission->deny();

					unset($denials[$key]);
				}
				else
				{
					// Not passed, remove it from current relations
					$this->permissions->removeElement($userPermission);
				}
			}
			else
			{
				// Already there, unset it
				unset($permissions[$key]);
			}
		}

		foreach ($denials as $newPermission)
		{
			$this->permissions->add($this->createUserPermission($newPermission, false));
		}

		foreach ($permissions as $newPermission)
		{
			$this->permissions->add($this->createUserPermission($newPermission));
		}
	}

	/**
	 * @return ArrayCollection
	 */
	public function getGroupPermissions()
	{
		$permissions = new ArrayCollection;

		foreach ($this->groups as $group)
		{
			foreach ($group->getPermissions() as $permission)
			{
				if (! $permissions->contains($permission))
				{
					$permissions->add($permission);
				}
			}
		}

		return $permissions;
	}

	/**
	 * @param string $email
	 *
	 * @return void
	 */
	public function changeEmail($email)
	{
		if (! filter_var($email, FILTER_VALIDATE_EMAIL))
		{
			throw new \UnexpectedValueException("Expected valid email, got $email");
		}

		$this->email = $email;
	}

	/**
	 * @param array|\Traversable $groups
	 *
	 * @return void
	 */
	public function setAllGroups($groups)
	{
		$this->groups->clear();

		foreach ($groups as $group)
		{
			$this->groups->add($group);
		}
	}

	/**
	 * @return void
	 */
	public function promoteToSuperUser()
	{
		$this->superUser = true;
	}

	/**
	 * Hard-coded UserPermission creation.
	 * Override this method to use custom UserPermission objects.
	 *
	 * @param string $permission
	 * @param bool   $allowed
	 *
	 * @return UserPermission
	 * @internal
	 */
	protected function createUserPermission($permission, $allowed = true)
	{
		return new UserPermission($this, $permission, $allowed);
	}

	/**
	 * Updates the user to the given group(s).
	 *
	 * @param  \Illuminate\Database\Eloquent\Collection $groups
	 * @param  bool                                     $remove
	 *
	 * @return bool
	 */
	public function updateGroups($groups, $remove = false)
	{
		if ($groups instanceof GroupInterface)
		{
			$groups = [$groups];
		}

		if ($remove)
		{
			$this->groups->clear();
		}

		foreach ($groups as $group)
		{
			if ($this->groups->contains($group))
			{
				continue;
			}

			if (! $this->addGroup($group))
			{
				return false;
			}
		}

		return true;
	}
}
