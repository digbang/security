<?php namespace Digbang\Security\Mappings;

use Digbang\Doctrine\Metadata\Builder;
use Digbang\Security\Permissions\PermissionCollection;
use Digbang\Security\Users\DefaultUser;

trait RoleMappingTrait
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

	/**
	 * Embeddable collection of user-specific permissions
	 * @type array
	 */
	protected $permissions = [PermissionCollection::class, 'permissions'];

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
			->uniqueString('slug')
			->string('name')
			->embedded($this->permissions[0], $this->permissions[1])
			->timestamps();
	}

	/**
	 * Adds only relations
	 *
	 * @param Builder $builder
	 */
	public function addRelations(Builder $builder)
	{
		$builder->belongsToMany($this->relations['users'][0], $this->relations['users'][1]);
	}
}
