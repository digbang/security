<?php namespace Digbang\Security\Users;

use Cartalyst\Sentinel\Users\UserRepositoryInterface;
use Doctrine\Common\Collections\Selectable;
use Doctrine\Common\Persistence\ObjectRepository;

interface UserRepository extends ObjectRepository, UserRepositoryInterface, Selectable
{

}
