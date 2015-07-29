<?php namespace Digbang\Security\Activations;

interface Activation
{
	/**
	 * @return void
	 */
	public function complete();

	/**
	 * Sentinel
	 * @return string
	 */
	public function __get();
}
