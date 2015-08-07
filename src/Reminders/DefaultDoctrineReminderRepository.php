<?php namespace Digbang\Security\Reminders;

use Cartalyst\Sentinel\Users\UserInterface;

class DefaultDoctrineReminderRepository extends DoctrineReminderRepository
{
	/**
	 * Get the Reminder class name.
	 * @return string
	 */
	protected function entityName()
	{
		return DefaultReminder::class;
	}

	/**
	 * Create a new reminder record and code.
	 *
	 * @param \Digbang\Security\Users\User $user
	 *
	 * @return Reminder
	 */
	public function create(UserInterface $user)
	{
		$reminder = new DefaultReminder($user);

        $this->save($reminder);

        return $reminder;
	}
}
