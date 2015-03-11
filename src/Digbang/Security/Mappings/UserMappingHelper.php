<?php namespace Digbang\Security\Mappings;

use Digbang\Doctrine\Metadata\Builder;
use Digbang\Doctrine\Metadata\Relations\BelongsToMany;
use Digbang\Doctrine\Metadata\Relations\HasMany;
use Digbang\Security\Entities\Group;
use Digbang\Security\Entities\UserPermission;
use Doctrine\ORM\Mapping\Builder\FieldBuilder;

final class UserMappingHelper
{
	use MappingHelper;

	/**
	 * @type string
	 */
	private $groupClassName;

	/**
	 * @type string
	 */
	private $userPermissionClass;

	/**
	 * @param string $groupClassName
	 * @param string $userPermissionClass
	 */
	public function __construct($groupClassName = Group::class, $userPermissionClass = UserPermission::class)
	{
		$this->groupClassName      = $groupClassName;
		$this->userPermissionClass = $userPermissionClass;
	}

	/**
	 * Adds all mappings: properties, relations and indexes
	 *
	 * @param Builder $builder
	 */
	public function addMappings(Builder $builder)
	{
		$this->addProperties($builder);
		$this->addRelations($builder);
		$this->addIndexes($builder);
	}

	/**
	 * Adds only properties
	 *
	 * @param Builder $builder
	 */
	public function addProperties(Builder $builder)
	{
		$builder->primary();
		$builder->string('email', function (FieldBuilder $fieldBuilder){
			$fieldBuilder->unique();
		});
		$builder->string('password');
		$builder->boolean('activated');
		$builder->boolean('superUser');
		$builder->string('activationCode', function (FieldBuilder $fieldBuilder){
			$fieldBuilder->nullable();
		});
		$builder->datetime('activatedAt', function (FieldBuilder $fieldBuilder){
			$fieldBuilder->nullable();
		});
		$builder->datetime('lastLogin', function (FieldBuilder $fieldBuilder){
			$fieldBuilder->nullable();
		});
		$builder->string('persistCode', function (FieldBuilder $fieldBuilder){
			$fieldBuilder->nullable();
		});
		$builder->string('resetPasswordCode', function (FieldBuilder $fieldBuilder){
			$fieldBuilder->nullable();
		});
		$builder->string('firstName', function (FieldBuilder $fieldBuilder){
			$fieldBuilder->nullable();
		});
		$builder->string('lastName', function (FieldBuilder $fieldBuilder){
			$fieldBuilder->nullable();
		});

		$builder->timestamps();
	}

	/**
	 * Adds only relations
	 *
	 * @param Builder $builder
	 */
	public function addRelations(Builder $builder)
	{
		$builder->belongsToMany($this->groupClassName, 'groups', function(BelongsToMany $belongsToMany){
			$belongsToMany->inversedBy('users');
			$belongsToMany->cascadePersist();

		});

		$builder->hasMany($this->userPermissionClass, 'permissions', function(HasMany $hasMany){
			$hasMany->mappedBy('user');
			$hasMany->cascadeAll();

			$this->orphanRemovalHack($hasMany);
		});
	}

	/**
	 * Adds only indexes
	 *
	 * @param Builder $builder
	 */
	public function addIndexes(Builder $builder)
	{
		$builder->addIndex(['activation_code'], 'backoffice_users_activation_code_index');
		$builder->addIndex(['reset_password_code'], 'backoffice_users_reset_password_code_index');
	}
}
