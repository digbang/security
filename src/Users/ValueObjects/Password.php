<?php namespace Digbang\Security\Users\ValueObjects;

class Password
{
	/**
	 * @type string
	 */
	private $hash;

	/**
	 * Password constructor.
	 *
	 * @param string $plain
	 */
	public function __construct($plain)
	{
		$this->hash = password_hash($plain, PASSWORD_DEFAULT);
	}

	/**
	 * @return string
	 */
	public function getHash()
	{
		return $this->hash;
	}

	/**
	 * @param string $password
	 *
	 * @return bool
	 */
	public function check($password)
	{
		return password_verify($password, $this->hash);
	}
}
