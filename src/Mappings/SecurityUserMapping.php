<?php

namespace Digbang\Security\Mappings;

abstract class SecurityUserMapping extends CustomTableMapping implements PermissibleMapping
{
    /**
     * Disable the roles relation.
     */
    abstract public function disableRoles();

    /**
     * Disable the throttles relation.
     */
    abstract public function disableThrottles();

    /**
     * Change the roles join table name.
     *
     * @param  string  $table
     */
    abstract public function changeRolesJoinTable($table);
}
