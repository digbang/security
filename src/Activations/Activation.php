<?php namespace Digbang\Security\Activations;

interface Activation
{
	/**
	 * @return void
	 */
	public function complete();

	/**
	 * Sentinel 2.0.6 will access $activation->code, so this must
	 * be implemented for now.
	 *
	 * @param string $name
	 * @return string
	 */
	public function __get($name);

	/**
	 * Get the unique code associated with this activation.
	 *
	 * @return string
	 */
	public function getCode();
}
