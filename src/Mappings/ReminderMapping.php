<?php
namespace Digbang\Security\Mappings;

use Digbang\Security\Reminders\DefaultReminder;
use Digbang\Security\Reminders\ReminderMappingTrait;
use LaravelDoctrine\Fluent\EntityMapping;
use LaravelDoctrine\Fluent\Fluent;

final class ReminderMapping extends EntityMapping implements CustomTableMapping
{
	use ReminderMappingTrait;

	/**
	 * @type string
	 */
	private $table;

	/**
	 * @return string
	 */
	public function getTable()
	{
		return $this->table;
	}

	/**
	 * @param string $table
	 */
	public function setTable($table)
	{
		$this->table = $table;
	}

	/**
	 * Returns the fully qualified name of the class that this mapper maps.
	 *
	 * @return string
	 */
	public function mapFor()
	{
		return DefaultReminder::class;
	}

	/**
	 * Load the object's metadata through the Metadata Builder object.
	 *
	 * @param Fluent $builder
	 */
	public function map(Fluent $builder)
	{
		if ($this->table)
		{
			$builder->table($this->table);
		}

		$this->addMappings($builder);
	}
}
