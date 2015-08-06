<?php namespace Digbang\Security\Roles;

use Digbang\Doctrine\Metadata\Builder;
use Digbang\Doctrine\Metadata\Relations\BelongsToMany;
use Digbang\Doctrine\Metadata\Relations\HasMany;
use Digbang\Security\Permissions\DefaultRolePermission;
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
		'users'       => [DefaultUser::class, 'users', 'roles'],
		'permissions' => [DefaultRolePermission::class, 'permissions'],
	];

	/**
	 * Needed for inverse mapping of hasMany relations.
	 *
	 * @type string
	 */
	protected $name = 'role';

	/**
	 * Enables or disables permissions mapping.
	 * @type bool
	 */
	protected $permissions = true;

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
			->timestamps();
	}

	/**
	 * Disable the permissions relation.
	 * @return void
	 */
	public function disablePermissions()
	{
		$this->permissions = false;
	}

	/**
	 * Adds only relations
	 *
	 * @param Builder $builder
	 */
	public function addRelations(Builder $builder)
	{
		$builder->belongsToMany($this->relations['users'][0], $this->relations['users'][1], function(BelongsToMany $belongsToMany){
			$belongsToMany->inversedBy($this->relations['users'][2]);
		});

		if ($this->permissions)
		{
			$builder->hasMany($this->relations['permissions'][0], $this->relations['permissions'][1], function(HasMany $hasMany){
				$hasMany
					->mappedBy($this->name)
					->cascadeAll()
					->orphanRemoval();
			});
		}
	}
}
