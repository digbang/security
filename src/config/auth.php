<?php
/**
 * Wrapper around Sentry config with some default configurations.
 * For full documentation, see the sentry manual
 * @see https://cartalyst.com/manual/sentry
 */
return [
	'hasher' => 'bcrypt',
	'cookie' => [
		'key' => 'db_backoffice_'
	],
	'groups' => [
		'model' => 'Cartalyst\Sentry\Groups\Eloquent\Group'
	],
	'users'  => [
		'model'           => 'Digbang\Security\Entities\User',
		'login_attribute' => 'email'
	],
	'user_groups_pivot_table' => 'users_groups',
	'throttling' => [
		'enabled'         => false,
		'model'           => 'Cartalyst\Sentry\Throttling\Eloquent\Throttle',
		'attempt_limit'   => 5,
		'suspension_time' => 15
	]
];