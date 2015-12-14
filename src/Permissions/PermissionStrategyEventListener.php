<?php namespace Digbang\Security\Permissions;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

class PermissionStrategyEventListener implements EventSubscriber
{
	/**
	 * @var \Closure
	 */
	private $permissionFactory;

	/**
	 * PermissionStrategyEventListener constructor.
	 *
	 * @param \Closure $permissionFactory
	 */
	public function __construct(\Closure $permissionFactory)
	{
		$this->permissionFactory = $permissionFactory;
	}

	/**
	 * Returns an array of events this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public function getSubscribedEvents()
	{
		return [Events::postLoad];
	}

	public function postLoad(LifecycleEventArgs $eventArgs)
	{
		$entity = $eventArgs->getEntity();

		if ($entity instanceof Permissible)
		{
			$entity->setPermissionsFactory($this->permissionFactory);
		}
	}
}
