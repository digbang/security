<?php namespace Digbang\Security\Entities;

use Cartalyst\Sentry\Users\Eloquent\User as SentryUser;
/**
 * Class User
 * @package Digbang\L4Backoffice\Auth
 */
class User extends SentryUser
{
	/**
	 * This method will return all the permissions that this
	 * user is allowed for. This means that denied permissions won't be there,
	 * and group permissions will be included.
	 */
	public function getAllPermissions()
	{
		$permissions = $this->getGroupPermissions();

		foreach ($this->permissions as $permission => $isAllowed)
		{
			if ($isAllowed == 1)
			{
				if (!array_key_exists($permission, $permissions))
				{
					$permissions[$permission] = $isAllowed;
				}
			}
			else
			{
				if (array_key_exists($permission, $permissions))
				{
					unset($permissions[$permission]);
				}
			}
		}

		return $permissions;
	}

	public function getGroupPermissions()
	{
		$permissions = [];

		foreach ($this->groups as $group)
		{
			$permissions = array_merge($permissions, $group->permissions);
		}

		return $permissions;
	}

	/**
	 * This method expects to receive the final array of allowed
	 * permissions.
	 * Internally, it will convert this to:
	 * 1:  A user permission not in any of this user's groups
	 * 0:  A user permission that's already in one of its groups
	 * -1: A group permission missing from the given permissions array
	 *
	 */
	public function setAllPermissions(array $newPermissions)
	{
		$newPermissions = array_combine($newPermissions, array_fill(0, count($newPermissions), 1));

		foreach ($this->getGroupPermissions() as $permission => $aOne)
		{
			if (!array_key_exists($permission, $newPermissions))
			{
				$newPermissions[$permission] = -1;
			}
		}

		$this->permissions = $newPermissions;
	}
} 