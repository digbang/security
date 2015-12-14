<?php namespace Digbang\Security\Factories;

use Cartalyst\Sentinel\Checkpoints\ActivationCheckpoint;
use Cartalyst\Sentinel\Checkpoints\CheckpointInterface;
use Cartalyst\Sentinel\Checkpoints\ThrottleCheckpoint;
use Cartalyst\Sentinel\Sentinel;
use Digbang\Security\Configurations\SecurityContextConfiguration;
use Digbang\Security\Security;
use Digbang\Security\Urls\PermissionAwareUrlGenerator;
use Illuminate\Contracts\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\Response;
use Illuminate\Routing\UrlGenerator;

class SecurityFactory
{
	/**
	 * @var Container
	 */
	private $container;

	/**
	 * @var RepositoryFactory
	 */
	private $repositories;

	/**
	 * @var UrlGenerator
	 */
	private $url;

	/**
	 * @param Container         $container
	 * @param RepositoryFactory $repositories
	 * @param UrlGenerator      $url
	 */
	public function __construct(Container $container, RepositoryFactory $repositories, UrlGenerator $url)
	{
		$this->container    = $container;
		$this->repositories = $repositories;
		$this->url = $url;
	}

	/**
	 * @param string $context
	 * @param SecurityContextConfiguration $configuration
	 * @return Security
	 */
	public function create($context, SecurityContextConfiguration $configuration)
	{
		$persistences = $this->repositories->createPersistenceRepository($context);
		$roles        = $this->repositories->createRoleRepository($context);
		$users        = $this->repositories->createUserRepository($context, $persistences, $roles);


		$sentinel = new Sentinel(
			$persistences,
			$users,
			$roles,
			$this->repositories->createActivationRepository($context),
			$this->container->make(Dispatcher::class)
		);

		foreach ($configuration->listCheckpoints() as $key => $checkpoint)
		{
			$sentinel->addCheckpoint($key, $this->makeCheckpoint($checkpoint, $configuration));
		}

		$sentinel->setReminderRepository(
			$this->repositories->createReminderRepository($context, $users)
		);

		$sentinel->setRequestCredentials(function(){
            $request = $this->container->make('request');

            $login    = $request->getUser();
            $password = $request->getPassword();

            if ($login === null && $password === null) {
                return;
            }

            return compact('login', 'password');
        });

		$sentinel->creatingBasicResponse(function(){
            $headers = ['WWW-Authenticate' => 'Basic'];

            return new Response('Invalid credentials.', 401, $headers);
        });

		$security = new Security(
			$sentinel,
			$this->repositories->createPermissionRepository($context)
		);

		$this->bindUrlGenerator($security);
		$security->setLoginRoute($configuration->getLoginRoute());

		return $security;
	}

	/**
	 * @param CheckpointInterface|string $checkpoint
	 * @param string $context
	 *
	 * @return ActivationCheckpoint|ThrottleCheckpoint|CheckpointInterface
	 */
	private function makeCheckpoint($checkpoint, $context)
	{
		switch ($checkpoint)
		{
			case ThrottleCheckpoint::class:
				return new ThrottleCheckpoint(
					$this->repositories->createThrottleRepository($context),
					$this->container->make('request')->getClientIp()
				);
			case ActivationCheckpoint::class:
				return new ActivationCheckpoint(
					$this->repositories->createActivationRepository($context)
				);
			default:
				return $this->container->make($checkpoint);
		}
	}

	/**
	 * @param Security $security
	 */
	private function bindUrlGenerator(Security $security)
	{
		$urls = new PermissionAwareUrlGenerator($this->url, $security);

		$security->setUrlGenerator($urls);
	}
}
