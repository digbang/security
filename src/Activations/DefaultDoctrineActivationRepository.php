<?php

namespace Digbang\Security\Activations;

use Cartalyst\Sentinel\Activations\ActivationInterface;
use Cartalyst\Sentinel\Users\UserInterface;

class DefaultDoctrineActivationRepository extends DoctrineActivationRepository
{
    protected const ENTITY_CLASSNAME = DefaultActivation::class;

    /**
     * Create a new activation record and code.
     *
     * @param UserInterface $user
     *
     * @return ActivationInterface
     */
    public function create(UserInterface $user): ActivationInterface
    {
        $entity = static::ENTITY_CLASSNAME;

        $activation = new $entity($user);

        $this->save($activation);

        return $activation;
    }

    /**
     * {@inheritdoc}
     */
    protected function entityName()
    {
        return static::ENTITY_CLASSNAME;
    }

    /**
     * Gets the activation for the given user.
     *
     * @param \Cartalyst\Sentinel\Users\UserInterface $user
     * @param string|null $code
     *
     * @return \Cartalyst\Sentinel\Activations\ActivationInterface|null
     */
    public function get(UserInterface $user, string $code = null): ?ActivationInterface
    {
        // TODO: Implement get() method.
    }
}
