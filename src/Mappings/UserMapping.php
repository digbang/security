<?php
namespace Digbang\Security\Mappings;

use Digbang\Security\Users\DefaultUser;
use Digbang\Security\Users\UserMappingTrait;
use LaravelDoctrine\Fluent\Fluent;

final class UserMapping extends SecurityUserMapping
{
	use UserMappingTrait;

	/**
	 * Returns the fully qualified name of the class that this mapper maps.
	 *
	 * @return string
	 */
	public function mapFor()
	{
		return DefaultUser::class;
	}

	/**
	 * Load the object's metadata through the Metadata Builder object.
	 *
	 * @param Fluent $builder
	 */
	public function map(Fluent $builder)
	{
		parent::map($builder);

		$this->addMappings($builder);
	}
}
