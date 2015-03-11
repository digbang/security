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
		'table' => 'groups',
		'model' => Digbang\Security\Entities\Group::class
	],
	'users'  => [
		'table'           => 'users',
		'model'           => Digbang\Security\Entities\User::class,
		'login_attribute' => 'email'
	],
	'user_groups_pivot_table' => 'user_group',
	'throttling' => [
		'enabled'         => false,
		'model'           => Digbang\Security\Entities\Throttle::class,
		'attempt_limit'   => 5,
		'suspension_time' => 15
	]
];