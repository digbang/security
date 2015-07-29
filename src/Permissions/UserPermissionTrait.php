<?php namespace Digbang\Security\Permissions;

use Digbang\Security\Contracts\Entities\User as UserInterface;

trait UserPermissionTrait
{
	use PermissionTrait {
		allows as doAllows;
	}

	/**
	 * @type UserInterface
	 */
	protected $user;

	/**
	 * @type bool
	 */
	protected $allowed = false;

	/**
	 * @param string $aPermission
	 *
	 * @return bool
	 */
	public function allows($aPermission)
	{
		if (! $this->allowed)
		{
			return false;
		}

		return $this->doAllows($aPermission);
	}

	/**
	 * @return void
	 */
	public function allow()
	{
		$this->allowed = true;
	}

	/**
	 * @return void
	 */
	public function deny()
	{
		$this->allowed = false;
	}

	/**
	 * @return bool
	 */
	public function isAllowed()
	{
		return $this->allowed;
	}
}
