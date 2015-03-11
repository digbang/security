<?php namespace Digbang\Security\Mappings;

use Digbang\Doctrine\Metadata\Builder;
use Digbang\Doctrine\Metadata\Relations\BelongsTo;
use Digbang\Security\Entities\User;
use Doctrine\ORM\Mapping\Builder\FieldBuilder;

final class ThrottleMappingHelper
{
	/**
	 * @type string
	 */
	private $userClassName;

	/**
	 * @type string
	 */
	private $userField;

	/**
	 * @type string
	 */
	private $foreignKey;

	/**
	 * @type string
	 */
	private $otherKey;

	function __construct($userClassName = User::class, $userField = 'user', $foreignKey = 'user_id', $otherKey = 'id')
	{
		$this->userClassName = $userClassName;
		$this->userField     = $userField;
		$this->foreignKey = $foreignKey;
		$this->otherKey = $otherKey;
	}

	public function addMappings(Builder $builder)
	{
		$builder->primary();
		$builder->string('ipAddress', function(FieldBuilder $fieldBuilder){
			$fieldBuilder->nullable();
		});
		$builder->integer('attempts');
		$builder->boolean('suspended');
		$builder->boolean('banned');
		$builder->datetime('lastAttemptAt', function (FieldBuilder $fieldBuilder){
			$fieldBuilder->nullable();
		});
		$builder->datetime('suspendedAt', function (FieldBuilder $fieldBuilder){
			$fieldBuilder->nullable();
		});
		$builder->datetime('bannedAt', function (FieldBuilder $fieldBuilder){
			$fieldBuilder->nullable();
		});

		$builder->belongsTo($this->userClassName, $this->userField, function (BelongsTo $belongsTo){
			$belongsTo->keys($this->foreignKey, $this->otherKey, true);
		});
	}
}
