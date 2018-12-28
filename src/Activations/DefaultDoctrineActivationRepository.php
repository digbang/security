<?php

namespace Digbang\Security\Activations;

use Cartalyst\Sentinel\Users\UserInterface;

class DefaultDoctrineActivationRepository extends DoctrineActivationRepository
{
    protected const ENTITY_CLASSNAME = DefaultActivation::class;

    /**
     * Create a new activation record and code.
     *
     * @param UserInterface $user
     * @return DefaultActivation
     */
    public function create(UserInterface $user)
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
}
