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

To use this package, you need to define a *Context* which you need to secure. URLs inside this
*Context* will have access to the `SecurityApi` configured for them.
This way, you may have multiple *Contexts* running on a single application.

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

And then refer to this context in your routing (as a *route middleware*):

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