<?php

namespace Digbang\Security\Reminders;

use Cartalyst\Sentinel\Reminders\ReminderRepositoryInterface;
use Doctrine\Persistence\ObjectRepository;

interface ReminderRepository extends ObjectRepository, ReminderRepositoryInterface
{
    /**
     * @param  int  $expires
     */
    public function setExpires($expires);
}
