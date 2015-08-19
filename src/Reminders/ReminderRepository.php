<?php namespace Digbang\Security\Reminders;

use Cartalyst\Sentinel\Reminders\ReminderRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;

interface ReminderRepository extends ObjectRepository, ReminderRepositoryInterface
{
	/**
	 * @param int $expires
	 * @return void
	 */
	public function setExpires($expires);
}
