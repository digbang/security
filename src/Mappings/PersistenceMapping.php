<?php

namespace Digbang\Security\Mappings;

use Digbang\Security\Persistences\DefaultPersistence;
use Digbang\Security\Persistences\PersistenceMappingTrait;
use LaravelDoctrine\Fluent\Fluent;

final class PersistenceMapping extends CustomTableMapping
{
    use PersistenceMappingTrait;

    /**
     * Returns the fully qualified name of the class that this mapper maps.
     *
     * @return string
     */
    public function mapFor()
    {
        return DefaultPersistence::class;
    }

    /**
     * Load the object's metadata through the Metadata Builder object.
     *
     * @param  Fluent  $builder
     */
    public function map(Fluent $builder)
    {
        parent::map($builder);

        $this->addMappings($builder);
    }
}
