<?php
use Illuminate\Support\Facades\Config;
return [
	/*
	|--------------------------------------------------------------------------
	| Default Authentication Driver
	|--------------------------------------------------------------------------
	|
	| This option controls the authentication driver that will be utilized.
	| This drivers manages the retrieval and authentication of the users
	| attempting to get access to protected areas of your application.
	|
	| Supported: "eloquent" (more coming soon).
	|
	*/

	'driver' => 'eloquent',

	/*
	|--------------------------------------------------------------------------
	| Default Hasher
	|--------------------------------------------------------------------------
	|
	| This option allows you to specify the default hasher used by Sentry
	|
	| Supported: "native", "bcrypt", "sha256", "whirlpool"
	|
	*/

	'hasher' => Config::get('security::auth.hasher'),

	/*
	|--------------------------------------------------------------------------
	| Cookie
	|--------------------------------------------------------------------------
	|
	| Configuration specific to the cookie component of Sentry.
	|
	*/

	'cookie' => [
		/*
		|--------------------------------------------------------------------------
		| Default Cookie Key
		|--------------------------------------------------------------------------
		|
		| This option allows you to specify the default cookie key used by Sentry.
		|
		| Supported: string
		|
		*/
		'key' => Config::get('security::auth.cookie.key'),

 	],

	/*
	|--------------------------------------------------------------------------
	| Groups
	|--------------------------------------------------------------------------
	|
	| Configuration specific to the group management component of Sentry.
	|
	*/

	'groups' => [

		/*
		|--------------------------------------------------------------------------
		| Model
		|--------------------------------------------------------------------------
		|
		| When using the "eloquent" driver, we need to know which
		| Eloquent models should be used throughout Sentry.
		|
		*/

		'model' => Config::get('security::auth.groups.model'),

	],

	/*
	|--------------------------------------------------------------------------
	| Users
	|--------------------------------------------------------------------------
	|
	| Configuration specific to the user management component of Sentry.
	|
	*/

	'users' => [

		/*
		|--------------------------------------------------------------------------
		| Model
		|--------------------------------------------------------------------------
		|
		| When using the "eloquent" driver, we need to know which
		| Eloquent models should be used throughout Sentry.
		|
		*/

		'model' => Config::get('security::auth.users.model'),

		/*
		|--------------------------------------------------------------------------
		| Login Attribute
		|--------------------------------------------------------------------------
		|
		| If you're using the "eloquent" driver and extending the base Eloquent
		| model, we allow you to globally override the login attribute without
		| even subclassing the model, simply by specifying the attribute below.
		|
		*/
		'login_attribute' => Config::get('security::auth.users.login_attribute')
	],

	/*
	|--------------------------------------------------------------------------
	| User Groups Pivot Table
	|--------------------------------------------------------------------------
	|
	| When using the "eloquent" driver, you can specify the table name
	| for the user groups pivot table.
	|
	| Default: users_groups
	|
	*/

	'user_groups_pivot_table' => Config::get('security::auth.user_groups_pivot_table'),

	/*
	|--------------------------------------------------------------------------
	| Throttling
	|--------------------------------------------------------------------------
	|
	| Throttling is an optional security feature for authentication, which
	| enables limiting of login attempts and the suspension & banning of users.
	|
	*/

	'throttling' => [

		/*
		|--------------------------------------------------------------------------
		| Throttling
		|--------------------------------------------------------------------------
		|
		| Enable throttling or not. Throttling is where users are only allowed a
		| certain number of login attempts before they are suspended. Suspension
		| must be removed before a new login attempt is allowed.
		|
		*/

		'enabled' => Config::get('security::auth.throttling.enabled'),

		/*
		|--------------------------------------------------------------------------
		| Model
		|--------------------------------------------------------------------------
		|
		| When using the "eloquent" driver, we need to know which
		| Eloquent models should be used throughout Sentry.
		|
		*/

		'model' => Config::get('security::auth.throttling.model'),

		/*
		|--------------------------------------------------------------------------
		| Attempts Limit
		|--------------------------------------------------------------------------
		|
		| When using the "eloquent" driver and extending the base Eloquent model,
		| you have the option to globally set the login attempts.
		|
		| Supported: int
		|
		*/

		'attempt_limit' => Config::get('security::auth.throttling.attempt_limit'),

		/*
		|--------------------------------------------------------------------------
		| Suspension Time
		|--------------------------------------------------------------------------
		|
		| When using the "eloquent" driver and extending the base Eloquent model,
		| you have the option to globally set the suspension time, in minutes.
		|
		| Supported: int
		|
		*/

		'suspension_time' => Config::get('security::auth.throttling.suspension_time')
	]
];
