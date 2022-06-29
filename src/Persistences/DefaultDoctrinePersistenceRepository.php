<?php

namespace Digbang\Security\Persistences;

use Digbang\Security\Users\User;

class DefaultDoctrinePersistenceRepository extends DoctrinePersistenceRepository
{
    protected const ENTITY_CLASSNAME = DefaultPersistence::class;

    /**
     * @inheritdoc
     */
    protected function entityName()
    {
        return static::ENTITY_CLASSNAME;
    }

    /**
     * @inheritdoc
     */
    protected function create(User $user, $code)
    {
        $entity = static::ENTITY_CLASSNAME;

        return new $entity($user, $code);
    }
}
