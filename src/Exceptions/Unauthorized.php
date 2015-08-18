<?php namespace Digbang\Security\Exceptions;

use Digbang\Security\Contracts\SecurityApi;

/**
 * Class AuthorizationException
 * @package Digbang\Security\Exceptions
 */
class Unauthorized extends \RuntimeException
{
	/**
	 * @type SecurityApi
	 */
	private $security;

	/**
	 * @type string
	 */
	private $permission;

	/**
	 * @param string      $permission
	 * @param SecurityApi $security
	 *
	 * @return static
	 */
	public static function permissionDenied($permission, SecurityApi $security)
	{
		$e = new static("Permission [$permission] permissionDenied.");

		$e->security   = $security;
		$e->permission = $permission;

		return $e;
	}

	/**
	 * @return string
	 */
	public function getPermission()
	{
		return $this->permission;
	}

	/**
	 * @return SecurityApi
	 */
	public function getSecurity()
	{
		return $this->security;
	}
}