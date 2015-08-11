<?php namespace Digbang\Security\Reminders;

use Cartalyst\Sentinel\Reminders\ReminderRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;

interface ReminderRepository extends ObjectRepository, ReminderRepositoryInterface
{

}
