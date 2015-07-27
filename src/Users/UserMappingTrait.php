<?php namespace Digbang\Security\Mappings;

use Digbang\Doctrine\Metadata\Builder;
use Digbang\Doctrine\Metadata\Relations\HasMany;
use Digbang\Security\Activations\Activation;
use Digbang\Security\Entities\Role;
use Digbang\Security\Permissions\PermissionCollection;
use Digbang\Security\Persistences\Persistence;
use Digbang\Security\Reminders\Reminder;
use Digbang\Security\Throttling\Throttle;

trait UserMappingTrait
{
	/**
	 * Needed for inverse mapping of hasMany relations.
	 *
	 * @type string
	 */
	protected $name = 'user';

	/**
	 * Relations mapping. Override this with your custom objects if needed.
	 * Each relation value needs a FQCN in position 0 and a field name in position 1.
	 *
	 * @type array
	 */
	protected $relations = [
		'roles'        => [Role::class,        'roles'],
		'persistences' => [Persistence::class, 'persistences'],
		'activations'  => [Activation::class,  'activations'],
		'reminders'    => [Reminder::class,    'reminders'],
		'throttles'    => [Throttle::class,    'throttles'],
	];

	/**
	 * Embeddable collection of user-specific permissions
	 * @type array
	 */
	protected $permissionCollection = [PermissionCollection::class, 'permissions'];

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
			->uniqueString('email')
			->string('password')
			->embedded($this->permissionCollection[0], $this->permissionCollection[1])
			->nullableDatetime('lastLogin')
			->nullableString('firstName')
			->nullableString('lastName')
			->timestamps();
	}

	/**
	 * Adds only relations
	 *
	 * @param Builder $builder
	 */
	public function addRelations(Builder $builder)
	{
		$this
			->hasMany('persistences', $builder)
			->hasMany('activations',  $builder)
			->hasMany('reminders',    $builder)
			->hasMany('throttles',    $builder);

		$builder->belongsToMany($this->relations['roles'][0], $this->relations['roles'][1]);
	}

	/**
	 * @param string  $key
	 * @param Builder $builder
	 * @return $this
	 */
	private function hasMany($key, Builder $builder)
	{
		$builder->hasMany($this->relations[$key][0], $this->relations[$key][1], function(HasMany $hasMany){
			$hasMany->mappedBy($this->name);
		});

		return $this;
	}
}
