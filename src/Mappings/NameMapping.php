<?php namespace Digbang\Security\Mappings;

use Digbang\Doctrine\Metadata\Builder;
use Digbang\Doctrine\Metadata\EmbeddableMapping;
use Digbang\Security\Users\ValueObjects\Name;

class NameMapping implements EmbeddableMapping
{
	/**
	 * Returns the fully qualified name of the embeddable that this mapper maps.
	 *
	 * @return string
	 */
	public function getEmbeddableName()
	{
		return Name::class;
	}

	/**
	 * Load the embeddable's metadata through the Metadata Builder object.
	 *
	 * @param Builder $builder
	 *
	 * @return void
	 */
	public function build(Builder $builder)
	{
		$builder
			->nullableString('firstName')
			->nullableString('lastName');
	}
}
