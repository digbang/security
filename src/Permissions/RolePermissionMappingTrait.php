<?php namespace Digbang\Security\Permissions;

use Digbang\Doctrine\Metadata\Builder;
use Digbang\Doctrine\Metadata\Relations\BelongsTo;
use Digbang\Security\Roles\DefaultRole;
use Doctrine\ORM\Mapping\Builder\FieldBuilder;

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
		'role' => [DefaultRole::class, 'role']
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
			->string('name', function(FieldBuilder $fieldBuilder){
				$fieldBuilder->makePrimaryKey();
			})
			->boolean('allowed');
	}

	/**
	 * Adds only relations
	 *
	 * @param Builder $builder
	 */
	public function addRelations(Builder $builder)
	{
		$builder->belongsTo($this->relations['role'][0], $this->relations['role'][1], function(BelongsTo $belongsTo){
			$belongsTo->isPrimaryKey();
		});
	}
}
