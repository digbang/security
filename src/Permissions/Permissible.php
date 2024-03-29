<?php

namespace Digbang\Security\Permissions;

use Cartalyst\Sentinel\Permissions\PermissibleInterface;
use Doctrine\Common\Collections\Collection;

interface Permissible extends PermissibleInterface
{
    /**
     * Set the Permissions instance factory configured for this context.
     *
     * @param  \Closure  $permissionsFactory
     */
    public function setPermissionsFactory(\Closure $permissionsFactory);

    /**
     * @param  string  $permission
     * @return bool
     */
    public function hasAccess($permission);

    /**
     * @param  string  $permission
     * @return bool
     */
    public function hasAnyAccess($permission);

    /**
     * @return Collection
     */
    public function getPermissions();

    /**
     * @param  array|string  $permissions
     * @param  bool  $force
     */
    public function allow($permissions, $force = false);

    /**
     * @param  array|string  $permissions
     * @param  bool  $force
     */
    public function deny($permissions, $force = false);

    /**
     * @param  array  $permissions
     */
    public function syncPermissions(array $permissions);
}
