<?php namespace Digbang\Security\Activations;

use Cartalyst\Sentinel\Activations\ActivationRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;

interface ActivationRepository extends ObjectRepository, ActivationRepositoryInterface
{
	/**
	 * @param int $expires
	 * @return void
	 */
	public function setExpires($expires);
}
