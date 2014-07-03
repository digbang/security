<?php namespace Digbang\Security\Contracts;

interface PermissionRepositoryInterface
{
	/**
	 * @param  string $url
	 * @return string The permission matching the url, if it needs one.
	 */
	public function getForUrl($url);
} 