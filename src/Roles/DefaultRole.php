<?php

namespace Digbang\Security\Roles;

use Digbang\Security\Permissions\DefaultRolePermission;
use Digbang\Security\Permissions\NullPermissions;
use Digbang\Security\Permissions\Permissible;
use Digbang\Security\Permissions\PermissibleTrait;
use Digbang\Security\Permissions\Permission;
use Digbang\Security\Support\TimestampsTrait;
use Digbang\Security\Users\DefaultUser;
use Doctrine\Common\Collections\ArrayCollection;
use Illuminate\Support\Str;
use IteratorAggregate;

class DefaultRole implements Role, Permissible
{
    use TimestampsTrait;
    use PermissibleTrait;

    /**
     * Probably unused, but part of the sentinel interface...
     *
     * @var string
     */
    protected static $usersModel = DefaultUser::class;

    /** @var int */
    protected $id;

    /** @var string */
    protected $name;

    /** @var string */
    protected $slug;

    /** @var ArrayCollection */
    protected $users;

    public function __construct($name, $slug = null)
    {
        $this->name = $name;
        $this->slug = $slug ?: Str::slug($name);

        $this->permissions = new ArrayCollection;
        $this->users = new ArrayCollection;

        $this->permissionsFactory = function () {
            return new NullPermissions;
        };
    }

    /**
     * @inheritdoc
     */
    public function getRoleId(): int
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getRoleSlug(): string
    {
        return $this->slug;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function getUsers(): IteratorAggregate
    {
        return $this->users;
    }

    /**
     * @return \Carbon\Carbon
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return \Carbon\Carbon
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @inheritdoc
     */
    public function setRoleSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * @param  array  $permissions
     */
    public function syncPermissions(array $permissions)
    {
        foreach ($this->permissions as $current) {
            /** @var Permission $current */
            if ($current->isAllowed() && ! in_array($current->getName(), $permissions)) {
                $this->removePermission($current);
            }
        }

        $this->allow($permissions);
    }

    public function update(string $name, array $permissions)
    {
        $this->setName($name);
        $this->syncPermissions($permissions);
    }

    /**
     * @inheritdoc
     */
    public function is($role)
    {
        if ($role instanceof Role) {
            return $this->getRoleId() == $role->getRoleId();
        }

        return $this->getRoleSlug() == $role;
    }

    /**
     * @inheritdoc
     */
    public static function getUsersModel(): string
    {
        return static::$usersModel;
    }

    /**
     * @inheritdoc
     */
    public static function setUsersModel(string $usersModel): void
    {
        static::$usersModel = $usersModel;
    }

    /**
     * @inheritdoc
     */
    protected function createPermission($permission, $value)
    {
        return new DefaultRolePermission($this, $permission, $value);
    }

    /**
     * @inheritdoc
     */
    protected function makePermissionsInstance()
    {
        $permissionsFactory = $this->getPermissionsFactory();

        if (! is_callable($permissionsFactory)) {
            throw new \InvalidArgumentException('No PermissionsFactory callable given. PermissionFactory callable should be set by the DoctrineRoleRepository on instance creation. New instances will use a NullPermissions implementation until persisted.');
        }

        return $permissionsFactory(null, [$this->permissions]);
    }
}
