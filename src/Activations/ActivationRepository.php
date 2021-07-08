<?php

namespace Digbang\Security\Activations;

use Cartalyst\Sentinel\Activations\ActivationRepositoryInterface;
use Cartalyst\Sentinel\Users\UserInterface;
use Doctrine\Persistence\ObjectRepository;

interface ActivationRepository extends ObjectRepository, ActivationRepositoryInterface
{
    /**
     * @param int $expires
     */
    public function setExpires($expires);

    public function completeAndUpdatePassword(UserInterface $user, string $code, string $password): bool;
}
