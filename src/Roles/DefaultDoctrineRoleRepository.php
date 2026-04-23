<?php

namespace Digbang\Security\Roles;

class DefaultDoctrineRoleRepository extends DoctrineRoleRepository
{
    protected const ENTITY_CLASSNAME = DefaultRole::class;

    /**
     * {@inheritdoc}
     */
    protected function entityName()
    {
        return static::ENTITY_CLASSNAME;
    }

    /**
     * {@inheritdoc}
     */
    protected function createRole($name, $slug = null)
    {
        $entity = static::ENTITY_CLASSNAME;

        return new $entity($name, $slug);
    }
}
