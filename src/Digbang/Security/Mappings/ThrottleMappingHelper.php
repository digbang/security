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

	function __construct($userClassName = User::class, $userField = 'user')
	{
		$this->userClassName = $userClassName;
		$this->userField     = $userField;
	}

	public function addMappings(Builder $builder)
	{
		$builder
			->primary()
			->nullableString('ipAddress')
			->integer('attempts')
			->boolean('suspended')
			->boolean('banned')
			->nullableDatetime('lastAttemptAt')
			->nullableDatetime('suspendedAt')
			->nullableDatetime('bannedAt')
			->mayBelongTo($this->userClassName, $this->userField);
	}
}
