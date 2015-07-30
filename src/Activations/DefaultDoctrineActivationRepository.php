<?php namespace Digbang\Security\Activations;

use Cartalyst\Sentinel\Users\UserInterface;
use Digbang\Security\Users\User;

class DefaultDoctrineActivationRepository extends DoctrineActivationRepository
{
	/**
	 * Create a new activation record and code.
	 *
	 * @param User $user
	 * @return DefaultActivation
	 */
	public function create(UserInterface $user)
	{
		$activation = new DefaultActivation($user);

		$this->save($activation);

		return $activation;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function entityName()
	{
		return DefaultActivation::class;
	}
}
