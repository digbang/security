<?php

namespace Digbang\Security\Persistences;

use Doctrine\Common\Collections\Collection;
use Illuminate\Support\Str;

trait PersistableTrait
{
    /**
     * @var Collection
     */
    protected $persistences;

    /**
     * @return Collection
     */
    public function getPersistences()
    {
        return $this->persistences;
    }

    /**
     * @inheritdoc
     */
    public function getPersistableKey(): string
    {
        return 'user_id';
    }

    /**
     * @inheritdoc
     */
    public function getPersistableRelationship(): string
    {
        return 'persistences';
    }

    /**
     * @inheritdoc
     */
    public function generatePersistenceCode(): string
    {
        return Str::random(32);
    }
}
