<?php namespace Digbang\Security\Activations;

use Cartalyst\Sentinel\Users\UserInterface;
use Digbang\Security\Users\DefaultUser;

class DefaultDoctrineActivationRepository extends DoctrineActivationRepository
{
	/**
	 * Create a new activation record and code.
	 *
	 * @param DefaultUser $user
	 *
*@return DefaultActivation
	 */
	public function create(UserInterface $user)
	{
		$activation = new DefaultActivation($user);

		$this->save($activation);

		return $activation;
	}

	/**
	 * Get the Activation class name.
	 * @return string
	 */
	protected function entityName()
	{
		return DefaultActivation::class;
	}
}
