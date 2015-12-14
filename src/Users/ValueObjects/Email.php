<?php namespace Digbang\Security\Users\ValueObjects;

class Email
{
	/**
	 * @var string
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

	/**
	 * The __toString method allows a class to decide how it will react when it is converted to a string.
	 *
	 * @return string
	 * @link http://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.tostring
	 */
	public function __toString()
	{
		return $this->getAddress();
	}
}
