Security
========
Security package for new laravel projects.

## Usage
Add the `SecurityServiceProvider` to your `config/app.php` file:

```php
	<?php
	return [
	    // ...
	    'providers' => [
	        // ...
	        Digbang\Security\Laravel\SecurityServiceProvider::class,
	    ],
	];
```

To use this package, you need to define a **Context** which you need to secure. URLs inside this
**Context** will have access to the `SecurityApi` configured for them.
This way, you may have multiple **Contexts** running on a single application.

Add as many contexts as you need in a `ServiceProvider :: boot()` of your own:

```php
	<?php namespace App\Providers;
	
	use Digbang\Security\SecurityContext;
	use Digbang\Security\Configurations\SecurityContextConfiguration;
	
	class MyServiceProvider extends \Illuminate\Support\ServiceProvider
	{
	    public function boot(SecurityContext $securityContext)
	    {
	        $configuration = new SecurityContextConfiguration('ecommerce');
	        
	        // customize the configuration object as needed...
	        
	        $securityContext->add($configuration);
	    }
	}
```

And then refer to this context in your routing (as a **route middleware**):

```php
	<?php
	/** @type \Illuminate\Routing\Router $router */
	$router->group(['middleware' => 'security:ecommerce'], function(Router $router){
	    // Controllers inside this routing group will be able to ask for an instance
	    // of the Digbang\Security\Contracts\SecurityApi interface.
	    $router->get('/', ['as' => 'foo', 'uses' => 'FooController@index']);
	});
```

The `Digbang\Security\Contracts\SecurityApi` interface gives access to all of this package's
functionality. In most cases, it works as a wrapper of the `Cartalyst\Sentinel\Sentinel` object.

Refer to the documentation in each method to understand its uses.

## Users
Basic authentication functionality is accessible directly through the `SecurityApi` object.

To access the `UserRepository` object, call `$securityApi->users()`.

### Reminders
Reminders are randomly generated codes related to a user, frequently used in reset password cycles.

To access the reminders functionality, use the `ReminderRepository` with `$securityApi->reminders()`. 

### Persistences
Persistences are session and cookie tokens generated to persist a logged-in session through time.

To access the persistences functionality, use the `PersistenceRepository` with `$securityApi->persistences()`. 

## Checkpoints
Checkpoints are custom logic to be executed every time a login attempt happens. The Security package
comes with **two** checkpoints: `Activations` and `Throttles`.

### Activations
The Activation checkpoint checks if a user has already activated his account every time he or she logs in.
When this check fails, a `NotActivatedException` is thrown.

To access the activations functionality, use the `ActivationRepository` with `$securityApi->activations()`.
 
### Throttling
The Throttling checkpoint monitors failed login attempts to prevent DDoS attacks. It logs *three* different
types of attempts, and reacts to each of them differently:

* `Global` attempts: All login attempts inside the configured context will log a global attempt.
* `IP` attempts: Attempts coming from the same IP will be logged to recognize possible attackers.
* `User` attempts: Multiple failed logins to the same user account will be logged to identify a possible victim.

Each type of attempt has two configurations:

* **Thresholds** (`int` or `array`): Represents the amount of attempts needed before the system is blocked. An array of `qty_attempts => block_time` can be used to block access for a given time based on the amount of failed attempts. 
* **Interval** (`int`): Represents the time (in seconds) that the system will block further attempts on this type.

You may change this configurations through the `SecurityContextConfiguration` object. The defaults are:

```php
<?php return [
	'global' => [
		'interval' => 900,
		'thresholds' => [
			10 => 1,
	        20 => 2,
	        30 => 4,
	        40 => 8,
	        50 => 16,
	        60 => 12
		]
	],
	'ip'     => [
		'interval' => 900,
		'thresholds' => 5
	],
	'user'   => [
		'interval' => 900,
		'thresholds' => 5
	]
];
```

To access the throttling functionality, use the `ThrottleRepository` with `$securityApi->throttles()`.

## Roles
Roles group users together and allow an administrator to give (or refuse) access to resources to a group of
users.

Roles may be disabled through the `SecurityContextConfiguration` object if not needed.

To access the roles functionality, use the `RoleRepository` with `$securityApi->roles()`.

## Permissions
Permissions are functionality identifiers that are used to grant or deny access to parts of the
system to specific users or roles.

By default, a `RoutePermissionRepository` object will check available permissions by parsing the routes
`action` array in search of a `permission` key. 
This strategy can be changed by implementing a different kind of `Digbang\Security\Permissions\PermissionRepository`,
and changing the `SecurityContextConfiguration` accordingly.

Permissions may also be disabled through the `SecurityContextConfiguration` object if not needed.

To access the permissions functionality, use the `PermissionRepository` with `$securityApi->permissions()`.

### Generating URLs
The `PermissibleUrlGenerator` is an extension of Laravel's `UrlGeneratorInterface` interface. The default
implementation, `PermissionAwareUrlGenerator`, will check if the currently logged-in user has access to the
requested url and throw a `Digbang\Security\Permissions\PermissionException` if he or she does not.
 
You may access this functionality through the `$securityApi->urls()` method. 

## Custom objects
The Security package extends the `Cartalyst\Sentinel` interfaces with more functionality. By default,
an implementation of each interface (eg.: `Digbang\Security\Users\User`) can be found in the same namespace
 (eg.:`Digbang\Security\Users\DefaultUser`.)

If you wish to use a custom implementation of any `Entity`, these are the steps you have to follow:

* you **must** extend the repository implementation (eg.: `Digbang\Security\Users\DoctrineUserRepository`) with one of your own, *OR*
* you **may** decide to implement the repository interface (eg.: `Digbang\Security\Users\UserRepository`) by yourself.
* you **must** implement all the methods in the corresponding interface (eg.: `Digbang\Security\Users\User`.)
* you **must** configure this in the `SecurityContextConfiguration` object, as shown above.
* you **may** reuse the entity trait (eg.: `Digbang\Security\Users\UserTrait`.) 
* you **may** reuse the mapping trait (eg.: `Digbang\Security\Users\UserMappingTrait`.)
