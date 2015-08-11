<?php namespace Digbang\Security\Throttling;

use Cartalyst\Sentinel\Throttling\ThrottleRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;

interface ThrottleRepository extends ObjectRepository, ThrottleRepositoryInterface
{

}
