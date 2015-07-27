<?php namespace Digbang\Security\Persistences;

use Digbang\Doctrine\Metadata\Builder;
use Digbang\Security\Users\User;

trait PersistenceMappingTrait
{
	/**
	 * Relations mapping. Override this with your custom objects if needed.
	 * Each relation value needs a FQCN in position 0 and a field name in position 1.
	 *
	 * @type array
	 */
	protected $relations = [
		'user' => [User::class, 'user']
	];

	/**
	 * Adds all mappings: properties and relations
	 *
	 * @param Builder $builder
	 */
	public function addMappings(Builder $builder)
	{
		$this->addProperties($builder);
		$this->addRelations($builder);
	}

	/**
	 * Adds only properties
	 *
	 * @param Builder $builder
	 */
	public function addProperties(Builder $builder)
	{
		$builder
			->primary()
			->uniqueString('code')
			->timestamps();
	}

	/**
	 * Adds only relations
	 *
	 * @param Builder $builder
	 */
	public function addRelations(Builder $builder)
	{
		$builder->belongsTo($this->relations['user'][0], $this->relations['user'][1]);
	}
}
