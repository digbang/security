<?php
namespace Digbang\Security\Users;

use Digbang\Security\Activations\DefaultActivation;
use Digbang\Security\Permissions\DefaultUserPermission;
use Digbang\Security\Roles\DefaultRole;
use Digbang\Security\Persistences\DefaultPersistence;
use Digbang\Security\Reminders\DefaultReminder;
use Digbang\Security\Throttling\DefaultUserThrottle;
use LaravelDoctrine\Fluent\Fluent;
use LaravelDoctrine\Fluent\Relations\OneToMany;

trait UserMappingTrait
{
	/**
	 * @var string
	 */
	protected $joinTable;

	protected $enabled = [
		'roles' => true,
		'throttles' => true,
		'permissions' => true
	];

	/**
	 * Needed for inverse mapping of hasMany relations.
	 *
	 * @var string
	 */
	protected $name = 'user';

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
		'roles'        => [DefaultRole::class,           'roles', 'users'],
		'persistences' => [DefaultPersistence::class,    'persistences'],
		'activations'  => [DefaultActivation::class,     'activations'],
		'reminders'    => [DefaultReminder::class,       'reminders'],
		'throttles'    => [DefaultUserThrottle::class,   'throttles'],
		'permissions'  => [DefaultUserPermission::class, 'permissions'],
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
		$builder->string('username');
		$builder->carbonDateTime('lastLogin')->nullable();
		$builder->carbonDateTime('createdAt');
		$builder->carbonDateTime('updatedAt');
		$builder->events()
			->prePersist('onPrePersist')
			->preUpdate('onPreUpdate');

		$builder->embed(ValueObjects\Email::class)->noPrefix();
		$builder->embed(ValueObjects\Name::class)->noPrefix();
		$builder->embed(ValueObjects\Password::class)->noPrefix();
	}

	/**
	 * Adds only relations
	 *
	 * @param Fluent $builder
	 */
	public function addRelations(Fluent $builder)
	{
		$this->hasMany('persistences', $builder)
			->cascadeAll()
			->orphanRemoval();

		$this->hasMany('reminders', $builder)
			->cascadeAll()
			->orphanRemoval();

		$this->hasMany('activations', $builder)
			->cascadeAll()
			->orphanRemoval()
			->orderBy('createdAt', 'desc');

		if ($this->enabled['throttles'])
		{
			$this->hasMany('throttles', $builder)
				->cascadeAll()
				->orphanRemoval();
		}

		if ($this->enabled['roles'])
		{
			$roles = $builder
				->belongsToMany($this->relations['roles'][0], $this->relations['roles'][1])
				->inversedBy($this->relations['roles'][2]);

			if ($this->joinTable)
			{
				$roles->joinTable($this->joinTable);
			}
		}

		if ($this->enabled['permissions'])
		{
			$this->hasMany('permissions', $builder)
				->cascadeAll()
				->orphanRemoval();
		}
	}

	/**
	 * Disable the roles relation.
	 * @return void
	 */
	public function disableRoles()
	{
		$this->enabled['roles'] = false;
	}

	/**
	 * Disable the throttles relation.
	 * @return void
	 */
	public function disableThrottles()
	{
		$this->enabled['throttles'] = false;
	}

	/**
	 * Disable the permissions relation.
	 * @return void
	 */
	public function disablePermissions()
	{
		$this->enabled['permissions'] = false;
	}

	/**
	 * @param string  $key
	 * @param Fluent $builder
	 * @return OneToMany
	 */
	private function hasMany($key, Fluent $builder)
	{
		return $builder
			->hasMany($this->relations[$key][0], $this->relations[$key][1])
			->mappedBy($this->name);
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
