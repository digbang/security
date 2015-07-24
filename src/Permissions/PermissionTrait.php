<?php namespace Digbang\Security\Entities;

trait PermissionTrait
{
	/**
	 * @type string
	 */
	protected $permission;

	/**
	 * @param string $aPermission
	 *
	 * @return bool
	 */
	public function allows($aPermission)
	{
		if (ends_with($this->permission, '*'))
		{
			return
				$this->permission != $aPermission &&
				starts_with($aPermission, substr($this->permission, 0, -1));
		}

		if (starts_with($this->permission, '*'))
		{
			return
				$this->permission != $aPermission &&
				ends_with($aPermission, substr($this->permission, 1));
		}

		if (ends_with($aPermission, '*'))
		{
			return
				$this->permission != $aPermission &&
				starts_with($this->permission, substr($aPermission, 0, -1));
		}

		if (starts_with($aPermission, '*'))
		{
			return
				$this->permission != $aPermission &&
				ends_with($this->permission, substr($aPermission, 1));
		}

		return $this->permission == $aPermission;
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->permission;
	}
}
