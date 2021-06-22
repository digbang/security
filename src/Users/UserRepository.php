<?php

namespace Digbang\Security\Users;

use Cartalyst\Sentinel\Users\UserRepositoryInterface;
use Doctrine\Common\Collections\Selectable;
use Doctrine\Persistence\ObjectRepository;

interface UserRepository extends ObjectRepository, UserRepositoryInterface, Selectable
{
    /**
     * @param User $user
     */
    public function destroy(User $user);
}
