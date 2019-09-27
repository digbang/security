<?php

namespace Digbang\Security\Reminders;

use Cartalyst\Sentinel\Users\UserInterface;

class DefaultDoctrineReminderRepository extends DoctrineReminderRepository
{
    protected const ENTITY_CLASSNAME = DefaultReminder::class;

    /**
     * Create a new reminder record and code.
     *
     * @param \Digbang\Security\Users\User $user
     *
     * @return Reminder
     */
    public function create(UserInterface $user)
    {
        $entity = static::ENTITY_CLASSNAME;

        $reminder = new $entity($user);

        $this->save($reminder);

        return $reminder;
    }

    /**
     * Get the Reminder class name.
     *
     * @return string
     */
    protected function entityName()
    {
        return static::ENTITY_CLASSNAME;
    }

    /**
     * Gets the reminder for the given user.
     *
     * @param \Cartalyst\Sentinel\Users\UserInterface $user
     * @param string|null $code
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function get(UserInterface $user, string $code = null)
    {
        // TODO: Implement get() method.
    }
}
