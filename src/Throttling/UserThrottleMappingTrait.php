<?php namespace Digbang\Security\Throttling;

use Digbang\Doctrine\Metadata\Builder;
use Digbang\Security\Users\User;

trait UserThrottleMappingTrait
{
	/**
	 * Relations mapping. Override this with your custom objects if needed.
	 * Each relation value needs a FQCN in position 0 and a field name in position 1.
	 *
	 * @type array
	 */
	private $relations = [
		'user' => [User::class, 'user']
	];

	public function addMappings(Builder $builder)
	{
		$builder->mayBelongTo($this->relations['user'][0], $this->relations['user'][1]);
	}
}
