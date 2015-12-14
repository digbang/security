<?php namespace Digbang\Security\Exceptions;

use Digbang\Security\Contracts\SecurityApi;

abstract class SecurityException extends \RuntimeException
{
	/**
	 * @var SecurityApi
	 */
	protected $security;

	/**
	 * @var string
	 */
	protected $context;

	/**
	 * @return SecurityApi
	 */
	public function getSecurity()
	{
		return $this->security;
	}

	/**
	 * @param SecurityApi $security
	 */
	protected function setSecurity($security)
	{
		$this->security = $security;
	}

	/**
	 * @return string
	 */
	public function getContext()
	{
		return $this->context;
	}

	/**
	 * @param string $context
	 * @return static
	 */
	public function inContext($context)
	{
		$this->context = $context;

		return $this;
	}
}
