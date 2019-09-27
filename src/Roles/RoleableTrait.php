<?php

namespace Digbang\Security\Roles;

use Doctrine\Common\Collections\ArrayCollection;
use IteratorAggregate;

trait RoleableTrait
{
    /**
     * @var ArrayCollection|IteratorAggregate|Role[]
     */
    protected $roles;

    /**
     * {@inheritdoc}
     */
    public function getRoles(): IteratorAggregate
    {
        return $this->roles;
    }

    /**
     * {@inheritdoc}
     */
    public function inRole($role): bool
    {
        return $this->roles->exists(function ($key, Role $myRole) use ($role) {
            return $myRole->is($role);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function addRole(Role $role)
    {
        if (! $this->inRole($role)) {
            $this->roles->add($role);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeRole(Role $role)
    {
        $this->roles->removeElement($role);
    }

    /**
     * Checks if the user is in any of the given roles.
     *
     * @param array $roles
     *
     * @return bool
     */
    public function inAnyRole(array $roles): bool
    {
        foreach ($roles as $rol) {
            $this->inRole($rol);
        }
    }
}
