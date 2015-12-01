<?php
namespace Digbang\Security\Mappings;

use Digbang\Security\Users\ValueObjects\Email;
use LaravelDoctrine\Fluent\EmbeddableMapping;
use LaravelDoctrine\Fluent\Fluent;

class EmailMapping extends EmbeddableMapping
{
	/**
	 * Returns the fully qualified name of the class that this mapper maps.
	 *
	 * @return string
	 */
	public function mapFor()
	{
		return Email::class;
	}

	/**
	 * Load the object's metadata through the Metadata Builder object.
	 *
	 * @param Fluent $builder
	 */
	public function map(Fluent $builder)
	{
		$builder
			->string('address')
			->columnName('email')
			->unique();
	}
}
