<?php

namespace Digbang\Security\Reminders;

interface Reminder
{
    /**
     * Complete a reminder and set the completion date.
     */
    public function complete();

    /**
     * Get the unique code for this reminder.
     *
     * @return string
     */
    public function getCode();
}
