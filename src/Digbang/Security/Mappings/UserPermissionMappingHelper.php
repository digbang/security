<?php namespace Digbang\Security\Mappings;

use Digbang\Doctrine\Metadata\Builder;
use Digbang\Doctrine\Metadata\Relations\BelongsTo;
use Digbang\Security\Entities\User;
use Doctrine\ORM\Mapping\Builder\FieldBuilder;

class UserPermissionMappingHelper
{
	use MappingHelper;
	/**
	 * @type string
	 */
	private $userClass;

	/**
	 * @param string $userClass
	 */
	public function __construct($userClass = User::class)
	{
		$this->userClass = $userClass;
	}

	/**
	 * @param Builder $builder
	 */
	public function addMappings(Builder $builder)
	{
		$builder->string('permission', function(FieldBuilder $fieldBuilder){
			$fieldBuilder->isPrimaryKey();
		});

		$builder->belongsTo($this->userClass, 'user', function(BelongsTo $belongsTo){
			$belongsTo->keys($this->foreignKey($this->userClass), 'id', false);

			$this->foreignIdentityHack($belongsTo);
		});

		$builder->boolean('allowed');
	}
}
