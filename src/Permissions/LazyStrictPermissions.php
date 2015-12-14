<?php namespace Digbang\Security\Permissions;

use Cartalyst\Sentinel\Permissions\PermissionsInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class LazyStrictPermissions implements PermissionsInterface
{
	use LazyPermissionsTrait;

	/**
	 * Create a new permissions instance.
	 *
	 * @param Collection $permissions
	 * @param array      $secondaryPermissions
	 */
	public function __construct(Collection $permissions = null, array $secondaryPermissions = [])
	{
		$this->permissions     = new ArrayCollection;
		$this->userPermissions = $permissions ?: new ArrayCollection;
		$this->rolePermissions = $secondaryPermissions;
	}

	/**
	 * Get the factory method to build this object.
	 * @return \Closure
	 */
	public static function getFactory()
	{
		return function(Collection $permissions = null, array $secondaryPermissions = []){
			return new static($permissions, $secondaryPermissions);
		};
	}

	/**
	 * @param Collection $permissions
	 * @param array      $secondaryPermissions
	 */
	protected function mergePermissions(Collection $permissions, array $secondaryPermissions = [])
	{
        foreach ($secondaryPermissions as $rolePermissions)
        {
            /** @var Collection $rolePermissions */
	        $rolePermissions->map(function(Permission $permission){
		        $this->add($permission, ! $permission->isAllowed());
	        });
        }

		$permissions->map(function(Permission $permission){
			$this->add($permission, ! $permission->isAllowed());
		});
	}
}
