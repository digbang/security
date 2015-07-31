<?php namespace Digbang\Security\Users\ValueObjects;

class Name
{
	/**
	 * @type string
	 */
	private $firstName;

	/**
	 * @type string
	 */
	private $lastName;

	/**
	 * Name constructor.
	 *
	 * @param string $firstName
	 * @param string $lastName
	 */
	public function __construct($firstName = '', $lastName = '')
	{
		$this->firstName = $firstName;
		$this->lastName  = $lastName;
	}

	/**
	 * @return string
	 */
	public function getFirstName()
	{
		return $this->firstName;
	}

	/**
	 * @return string
	 */
	public function getLastName()
	{
		return $this->lastName;
	}

	/**
	 * @param string $separator
	 *
	 * @return string
	 */
	public function getFullName($separator = ' ')
	{
		return $this->firstName . $separator . $this->lastName;
	}
}
