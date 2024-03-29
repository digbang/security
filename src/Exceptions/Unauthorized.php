<?php

namespace Digbang\Security\Exceptions;

use Digbang\Security\Contracts\SecurityApi;

/**
 * Class Unauthorized.
 */
class Unauthorized extends SecurityException
{
    /**
     * @var string
     */
    private $permission;

    /**
     * @param  string  $permission
     * @param  SecurityApi  $security
     * @return static
     */
    public static function permissionDenied($permission, SecurityApi $security)
    {
        $e = new static("Permission [$permission] permissionDenied.");

        $e->setSecurity($security);
        $e->setPermission($permission);

        return $e;
    }

    /**
     * @return string
     */
    public function getPermission()
    {
        return $this->permission;
    }

    /**
     * @param  string  $permission
     */
    private function setPermission($permission)
    {
        $this->permission = $permission;
    }
}
