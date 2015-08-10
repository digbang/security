<?php namespace Digbang\Security\Activations;

/**
 * Interface Activation
 *
 * @package Digbang\Security\Activations
 * @property-read string $code
 */
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

	/**
	 * @return \Carbon\Carbon
	 */
	public function getCreatedAt();

	/**
	 * @return bool
	 */
	public function isCompleted();

	/**
	 * @return \Carbon\Carbon
	 */
	public function getCompletedAt();
}
