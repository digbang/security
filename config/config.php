<?php
return [
	'permissions' => [
		'repository' => Digbang\Security\Permissions\InsecurePermissionRepository::class,
		'prefix'     => 'backoffice'
	],
	'emails' => [
		'from' => [
			'address' => 'backoffice@digbang.com',
			'name'    => 'Backoffice'
		],
		'activation' => [
			'subject' => 'Activate your account'
		],
		'password-reset' => [
			'subject' => 'Reset your password'
		]
	],
	'auth' => [
		'login_route' => 'backoffice.auth.login'
	]
];
