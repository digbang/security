<?php

namespace Digbang\Security\Roles;

use Cartalyst\Sentinel\Roles\RoleableInterface;

interface Roleable extends RoleableInterface
{
    /**
     * Add a role to the entity.
     *
     * @param  Role  $role
     */
    public function addRole(Role $role);

    /**
     * Remove a role from the entity.
     *
     * @param  Role  $role
     */
    public function removeRole(Role $role);
}
