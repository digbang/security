<?php namespace Digbang\Security\Permissions;

use Digbang\Doctrine\Metadata\Builder;
use Digbang\Doctrine\Metadata\Relations\BelongsToMany;
use Digbang\Security\Roles\DefaultRole;

trait RolePermissionMappingTrait
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
		'roles' => [DefaultRole::class, 'roles']
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
			->string('name')
			->boolean('allowed');
	}

	/**
	 * Adds only relations
	 *
	 * @param Builder $builder
	 */
	public function addRelations(Builder $builder)
	{
		$builder->belongsToMany($this->relations['roles'][0], $this->relations['roles'][0], function(BelongsToMany $belongsToMany){
			$belongsToMany->orphanRemoval();
		});
	}
}
