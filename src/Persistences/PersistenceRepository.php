<?php

namespace Digbang\Security\Persistences;

use Cartalyst\Sentinel\Persistences\PersistenceRepositoryInterface;
use Doctrine\Persistence\ObjectRepository;

interface PersistenceRepository extends ObjectRepository, PersistenceRepositoryInterface
{
    public function setPersistenceMode($mode = 'single');
}
