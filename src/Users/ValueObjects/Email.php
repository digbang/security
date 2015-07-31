<?php namespace Digbang\Security\Users\ValueObjects;

class Email
{
	/**
	 * @type string
	 */
	private $address;

	/**
	 * Email constructor.
	 *
	 * @param string $address
	 */
	public function __construct($address)
	{
		if (! filter_var($address, FILTER_VALIDATE_EMAIL))
		{
			throw new \InvalidArgumentException("Given email address is not valid: [$address].");
		}

		$this->address = $address;
	}

	/**
	 * @return string
	 */
	public function getAddress()
	{
		return $this->address;
	}
}
