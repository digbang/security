<?php namespace Digbang\Security\Mappings;
use Digbang\Doctrine\Metadata\Builder;
use Digbang\Doctrine\Metadata\EmbeddableMapping;
use Digbang\Security\Users\ValueObjects\Email;
use Doctrine\ORM\Mapping\Builder\FieldBuilder;

class EmailMapping implements EmbeddableMapping
{
	/**
	 * Returns the fully qualified name of the embeddable that this mapper maps.
	 *
	 * @return string
	 */
	public function getEmbeddableName()
	{
		return Email::class;
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
		$builder->string('address', function(FieldBuilder $fieldBuilder){
			$fieldBuilder->columnName('email');
		});

		$builder->addUniqueConstraint(['email'], uniqid('email_unique_'));
	}
}
