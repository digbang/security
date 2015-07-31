<?php namespace Digbang\Security\Permissions;

interface Permission
{
	/**
	 * @return string
	 */
	public function getName();

	/**
	 * @return bool
	 */
	public function isAllowed();
}
