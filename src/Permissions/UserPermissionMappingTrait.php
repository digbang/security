<?php namespace Digbang\Security\Permissions;

use Digbang\Doctrine\Metadata\Builder;
use Digbang\Doctrine\Metadata\Relations\BelongsToMany;
use Digbang\Security\Users\DefaultUser;

trait UserPermissionMappingTrait
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
	protected $relations = [
		'users' => [DefaultUser::class, 'users']
	];

	public function addMappings(Builder $builder)
	{
		$this->addProperties($builder);
		$this->addRelations($builder);
	}

	public function addProperties(Builder $builder)
	{
		$builder
			->primary()
			->string('name')
			->boolean('allowed');
	}

	public function addRelations(Builder $builder)
	{
		$builder->belongsToMany($this->relations['users'][0], $this->relations['users'][0], function(BelongsToMany $belongsToMany){
			$belongsToMany->orphanRemoval();
		});
	}
}
