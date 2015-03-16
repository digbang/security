<?php namespace Digbang\Security\Entities;

use Digbang\Security\Contracts\Group as GroupInterface;
use Digbang\Security\Contracts\RepositoryAware;
use Doctrine\Common\Collections\ArrayCollection;

class Group implements GroupInterface, RepositoryAware
{
	use GroupTrait;

	/**
	 * @param string $name
	 * @param array  $permissions
	 */
	public function __construct($name, array $permissions = [])
	{
		$this->name        = $name;
		$this->permissions = new ArrayCollection();

		if (!empty($permissions))
		{
			$this->setPermissions($permissions);
		}
	}

	/**
	 * @param string $name
	 * @param array  $permissions
	 *
	 * @return GroupInterface
	 */
	public static function create($name, array $permissions = [])
	{
		return new static($name, $permissions);
	}
}
