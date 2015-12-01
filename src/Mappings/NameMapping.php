<?php
namespace Digbang\Security\Mappings;

use Digbang\Security\Users\ValueObjects\Name;
use LaravelDoctrine\Fluent\EmbeddableMapping;
use LaravelDoctrine\Fluent\Fluent;

class NameMapping extends EmbeddableMapping
{
	/**
	 * Returns the fully qualified name of the class that this mapper maps.
	 *
	 * @return string
	 */
	public function mapFor()
	{
		return Name::class;
	}

	/**
	 * Load the object's metadata through the Metadata Builder object.
	 *
	 * @param Fluent $builder
	 */
	public function map(Fluent $builder)
	{
		$builder->string('firstName')->nullable();
		$builder->string('lastName')->nullable();
	}
}
