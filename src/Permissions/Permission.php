<?php

namespace Digbang\Security\Permissions;

interface Permission
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return bool
     */
    public function isAllowed();

    /**
     * Allow this permission.
     */
    public function allow();

    /**
     * Deny this permission.
     */
    public function deny();

    /**
     * Compares two permissions and returns TRUE if they are equal.
     *
     * @param  Permission  $permission
     * @return bool
     */
    public function equals(Permission $permission);
}
