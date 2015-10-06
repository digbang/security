<?php namespace Digbang\Security\Permissions;

abstract class DefaultPermission implements Permission
{
	/**
	 * @type string
	 */
	protected $name;

	/**
	 * @type bool
	 */
	protected $allowed = true;

	/**
	 * @param string $name
	 * @param bool   $allowed
	 */
	public function __construct($name, $allowed = true)
	{
		$this->name    = $name;
		$this->allowed = $allowed;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return bool
	 */
	public function isAllowed()
	{
		return $this->allowed;
	}

	/**
	 * Return the name as the string representation.
	 */
	public function __toString()
	{
		return $this->getName();
	}
}
