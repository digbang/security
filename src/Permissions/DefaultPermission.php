<?php namespace Digbang\Security\Permissions;

abstract class DefaultPermission implements Permission
{
	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var bool
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
	 * {@inheritdoc}
	 */
	public function allow()
	{
		$this->allowed = true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function deny()
	{
		$this->allowed = false;
	}

	public function equals(Permission $permission)
	{
		if ($permission->getName() != $this->name)
		{
			return false;
		}

		// True if allow is equal ((true && true) || (false && false))
		return !($this->isAllowed() XOR $permission->isAllowed());
	}

	/**
	 * Return the name as the string representation.
	 */
	public function __toString()
	{
		return $this->getName();
	}
}
