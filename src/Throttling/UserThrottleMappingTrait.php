<?php namespace Digbang\Security\Throttling;

use Digbang\Doctrine\Metadata\Builder;
use Digbang\Security\Users\DefaultUser;

trait UserThrottleMappingTrait
{
	/**
	 * Relations mapping. Override this with your custom objects if needed.
	 * Each relation value needs a FQCN in position 0 and a field name in position 1.
	 *
	 * IMPORTANT: Relations will NOT be iterated! Each object will access its specific
	 *            relation keys.
	 *
	 * @type array
	 */
	private $relations = [
		'user' => [DefaultUser::class, 'user']
	];

	public function addMappings(Builder $builder)
	{
		$builder->mayBelongTo($this->relations['user'][0], $this->relations['user'][1]);
	}
}
