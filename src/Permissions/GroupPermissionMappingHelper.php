<?php namespace Digbang\Security\Mappings;

use Digbang\Doctrine\Metadata\Builder;
use Digbang\Doctrine\Metadata\Relations\BelongsTo;
use Digbang\Security\Entities\Role;
use Doctrine\ORM\Mapping\Builder\FieldBuilder;

class GroupPermissionMappingHelper
{
	use MappingHelper;

	private $groupClass;

	function __construct($groupClass = Role::class)
	{
		$this->groupClass = $groupClass;
	}

	public function addMappings(Builder $builder)
	{
		$builder
			->string('permission', function(FieldBuilder $fieldBuilder){
				$fieldBuilder->makePrimaryKey();
			})
			->belongsTo($this->groupClass, 'group', function(BelongsTo $belongsTo){
				$belongsTo->isPrimaryKey();
			});
	}
}
