<?php namespace Digbang\Security\Entities;

use Cartalyst\Sentry\Groups\Eloquent\Group as SentryGroup;
/**
 * Class Group
 * @package Digbang\L4Backoffice\Auth
 */
class Group extends SentryGroup
{
	public function __construct(array $attributes = array())
	{
		$this->setUserModel(\Config::get('security::auth.users.model', 'Digbang\Security\Entities\User'));
		$this->setTable(\Config::get('security::auth.groups.table', 'groups'));
		$this->setUserGroupsPivot(\Config::get('security::auth.user_groups_pivot_table', 'users_groups'));

		parent::__construct($attributes);
	}
} 