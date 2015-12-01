<?php
namespace Digbang\Security\Roles;

use Digbang\Security\Permissions\DefaultRolePermission;
use Digbang\Security\Users\DefaultUser;
use LaravelDoctrine\Fluent\Fluent;

trait RoleMappingTrait
{
	/**
	 * @type string
	 */
	protected $joinTable;

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
		$builder->string('slug')->unique();
		$builder->string('name');
		$builder->carbonDateTime('createdAt');
		$builder->carbonDateTime('updatedAt');
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
	 * @param Fluent $builder
	 */
	public function addRelations(Fluent $builder)
	{
		$users = $builder
			->belongsToMany($this->relations['users'][0], $this->relations['users'][1])
			->mappedBy($this->relations['users'][2]);

		if ($this->joinTable)
		{
			$users->joinTable($this->joinTable);
		}

		if ($this->permissions)
		{
			$builder
				->hasMany($this->relations['permissions'][0], $this->relations['permissions'][1])
				->mappedBy($this->name)
				->cascadeAll()
				->orphanRemoval();
		}
	}

	/**
	 * Change the roles join table name.
	 *
	 * @param string $table
	 */
	public function changeRolesJoinTable($table)
	{
		$this->joinTable = $table;
	}
}
