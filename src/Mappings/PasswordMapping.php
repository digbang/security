<?php namespace Digbang\Security\Mappings;

use Digbang\Doctrine\Metadata\Builder;
use Digbang\Doctrine\Metadata\EmbeddableMapping;
use Digbang\Security\Users\ValueObjects\Password;
use Doctrine\ORM\Mapping\Builder\FieldBuilder;

final class PasswordMapping implements EmbeddableMapping
{
	/**
	 * Returns the fully qualified name of the embeddable that this mapper maps.
	 *
	 * @return string
	 */
	public function getEmbeddableName()
	{
		return Password::class;
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
		$builder->string('hash', function(FieldBuilder $fieldBuilder){
			$fieldBuilder->columnName('password');
		});
	}
}
