<?php namespace Digbang\Security\Permissions;

class InsecurePermissionRepository implements PermissionRepository
{
	/**
	 * {@inheritdoc}
	 */
	public function getForRoute($route)
	{
		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getForAction($action)
	{
		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function all()
	{
		return [];
	}

	/**
	 * {@inheritdoc}
	 */
	public function getForPath($path)
	{
		return null;
	}
}
