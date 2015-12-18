<?php
namespace Digbang\Security\Activations;

use Digbang\Security\Users\DefaultUser;
use LaravelDoctrine\Fluent\Fluent;

trait ActivationMappingTrait
{
	/**
	 * Relations mapping. Override this with your custom objects if needed.
	 * Each relation value needs a FQCN in position 0 and a field name in position 1.
	 *
	 * IMPORTANT: Relations will NOT be iterated! Each object will access its specific
	 *            relation keys.
	 *
	 * @var array
	 */
	protected $relations = [
		'user' => [DefaultUser::class, 'user']
	];

	/**
	 * Adds all mappings: properties and relations
	 *
	 * @param Fluent $builder
	 */
	public function addMappings(Fluent $builder)
	{
		$this->addProperties($builder);
		$this->addRelations($builder);
	}

	/**
	 * Adds only properties
	 *
	 * @param Fluent $builder
	 */
	public function addProperties(Fluent $builder)
	{
		$builder->bigIncrements('id');
		$builder->string('code');
		$builder->boolean('completed');
		$builder->carbonDateTime('completedAt')->nullable();
		$builder->carbonDateTime('createdAt');
		$builder->carbonDateTime('updatedAt');

		$builder->events()
			->prePersist('onPrePersist')
			->preUpdate('onPreUpdate');
	}

	/**
	 * Adds only relations
	 *
	 * @param Fluent $builder
	 */
	public function addRelations(Fluent $builder)
	{
		$builder->belongsTo($this->relations['user'][0], $this->relations['user'][1]);
	}
}
