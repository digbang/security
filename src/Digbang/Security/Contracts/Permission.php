<?php namespace Digbang\Security\Contracts;

interface Permission
{
	/**
	 * @param $aPermission
	 *
	 * @return bool
	 */
	public function allows($aPermission);
}
