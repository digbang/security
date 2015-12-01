<?php
namespace Digbang\Security\Mappings;

use Digbang\Security\Users\ValueObjects\Password;
use LaravelDoctrine\Fluent\EmbeddableMapping;
use LaravelDoctrine\Fluent\Fluent;

final class PasswordMapping extends EmbeddableMapping
{
	/**
	 * Returns the fully qualified name of the class that this mapper maps.
	 *
	 * @return string
	 */
	public function mapFor()
	{
		return Password::class;
	}

	/**
	 * Load the object's metadata through the Metadata Builder object.
	 *
	 * @param Fluent $builder
	 */
	public function map(Fluent $builder)
	{
		$builder->string('hash')->columnName('password');
	}
}
