<?php namespace Digbang\Security\Reminders;

interface Reminder
{
	/**
	 * Complete a reminder and set the completion date.
	 *
	 * @return void
	 */
	public function complete();
}
