<?php

namespace Digbang\Security\Users;

use Carbon\Carbon;
use Digbang\Security\Activations\Activation;
use Digbang\Security\Permissions\DefaultUserPermission;
use Digbang\Security\Permissions\NullPermissions;
use Digbang\Security\Permissions\Permissible;
use Digbang\Security\Permissions\PermissibleTrait;
use Digbang\Security\Permissions\Permission;
use Digbang\Security\Persistences\Persistable;
use Digbang\Security\Persistences\PersistableTrait;
use Digbang\Security\Roles\Role;
use Digbang\Security\Roles\Roleable;
use Digbang\Security\Roles\RoleableTrait;
use Digbang\Security\Support\TimestampsTrait;
use Digbang\Security\Throttling\Throttleable;
use Digbang\Security\Throttling\ThrottleableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class DefaultUser implements User, Roleable, Permissible, Persistable, Throttleable
{
    use TimestampsTrait;
    use PersistableTrait;
    use PermissibleTrait;
    use ThrottleableTrait;
    use RoleableTrait {
        addRole    as _addRole;
        removeRole as _removeRole;
    }

    /** @var int */
    protected $id;

    /** @var ValueObjects\Email */
    protected $email;

    /** @var string */
    protected $username;

    /** @var ValueObjects\Password */
    protected $password;

    /** @var ValueObjects\Name */
    protected $name;

    /** @var Carbon|null */
    protected $lastLogin;

    /** @var Carbon|null */
    protected $passwordExpiration;

    /** @var ArrayCollection */
    protected $activations;

    /** @var ArrayCollection */
    protected $reminders;

    public function __construct(string $email, string $password, string $username, ?Carbon $passwordExpiration = null)
    {
        $this->email = new ValueObjects\Email($email);
        $this->changePassword($password, $passwordExpiration);
        $this->changeUsername($username);

        $this->roles = new ArrayCollection;
        $this->permissions = new ArrayCollection;
        $this->persistences = new ArrayCollection;
        $this->activations = new ArrayCollection;
        $this->reminders = new ArrayCollection;
        $this->throttles = new ArrayCollection;
        $this->name = new ValueObjects\Name;
        $this->permissionsFactory = function () {
            return new NullPermissions;
        };
    }

    public function __toString(): string
    {
        return (string) $this->getName()->getFullName();
    }

    /**
     * @param  ValueObjects\Name  $name
     */
    public function setName(ValueObjects\Name $name)
    {
        $this->name = $name;
    }

    public function changeName(?string $firstName = null, ?string $lastName = null)
    {
        $this->setName(new ValueObjects\Name($firstName, $lastName));
    }

    /**
     * @return ValueObjects\Name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->name->getFirstName();
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->name->getLastName();
    }

    /**
     * @inheritdoc
     *
     * @throws \InvalidArgumentException
     */
    public function update(array $credentials)
    {
        if (array_key_exists('email', $credentials)) {
            $this->email = new ValueObjects\Email($credentials['email']);
        }

        if (array_key_exists('username', $credentials)) {
            $this->changeUsername($credentials['username']);
        }

        if (array_key_exists('password', $credentials) && ! empty($credentials['password'])) {
            $expiration = null;
            if (array_key_exists('password_expiration', $credentials) && ! empty($credentials['password_expiration'])) {
                $expiration = $credentials['password_expiration'];
            }

            $this->changePassword($credentials['password'], $expiration);
        }

        if (array_key_exists('permissions', $credentials)) {
            $this->syncPermissions((array) $credentials['permissions']);
        }

        $this->changeName(
            Arr::get($credentials, 'firstName', $this->name->getFirstName()),
            Arr::get($credentials, 'lastName', $this->name->getLastName())
        );
    }

    /**
     * @inheritdoc
     */
    public function getUserId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->getUserId();
    }

    /**
     * @inheritdoc
     */
    public function getUserLogin(): string
    {
        return $this->getEmail();
    }

    /**
     * @inheritdoc
     */
    public function getUserLoginName(): string
    {
        return 'email';
    }

    /**
     * @inheritdoc
     */
    public function getUserPassword(): string
    {
        return $this->password->getHash();
    }

    /**
     * @inheritdoc
     */
    public function checkPassword($password)
    {
        return $this->password->check($password);
    }

    /**
     * @inheritdoc
     */
    public function getPersistableId(): string
    {
        return (string) $this->getUserId();
    }

    /**
     * @return Carbon|null
     */
    public function getLastLogin(): ?Carbon
    {
        return $this->lastLogin;
    }

    /**
     * @inheritdoc
     */
    public function recordLogin()
    {
        $this->lastLogin = Carbon::now();
    }

    public function getPasswordExpiration(): ?Carbon
    {
        return $this->passwordExpiration;
    }

    public function setPasswordExpiration(?Carbon $expiration = null): void
    {
        $this->passwordExpiration = $expiration;
    }

    public function hasExpiredPassword(): bool
    {
        return $this->passwordExpiration !== null && $this->passwordExpiration->isPast();
    }

    /**
     * @inheritdoc
     */
    public function syncPermissions(array $permissions)
    {
        foreach ($this->permissions as $current) {
            /** @var Permission $current */
            if ($current->isAllowed() && ! in_array($current->getName(), $permissions)) {
                $current->deny();
            } elseif (! $current->isAllowed() && in_array($current->getName(), $permissions)) {
                $current->allow();
            }
        }

        $this->roles->map(function (Role $role) use ($permissions) {
            if ($role instanceof Permissible) {
                $rolePermissions = $role->getPermissions();
                $rolePermissions
                    ->filter(function (Permission $permission) use ($permissions) {
                        return $permission->isAllowed() && ! in_array($permission->getName(), $permissions);
                    })
                    ->map(function (Permission $permission) {
                        $this->addPermission($permission->getName(), false);
                    });

                $rolePermissions->map(function (Permission $permission) {
                    $this->permissions->filter(function (Permission $current) use ($permission) {
                        return $current->equals($permission);
                    })->map(function (Permission $repeated) {
                        $this->permissions->removeElement($repeated);
                    });
                });
            }
        });

        $this->refreshPermissionsInstance();

        $this->allow($permissions);
    }

    /**
     * @inheritdoc
     */
    public function addRole(Role $role)
    {
        $this->_addRole($role);

        $this->refreshPermissionsInstance();
    }

    /**
     * @inheritdoc
     */
    public function removeRole(Role $role)
    {
        $this->_removeRole($role);

        $this->refreshPermissionsInstance();
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email->getAddress();
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
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
     * @return ArrayCollection
     */
    public function getActivations()
    {
        return $this->activations;
    }

    /**
     * @return ArrayCollection
     */
    public function getReminders()
    {
        return $this->reminders;
    }

    /**
     * @inheritdoc
     */
    public function isActivated()
    {
        return $this->activations->exists(function ($id, Activation $activation) {
            return $activation->isCompleted();
        });
    }

    /**
     * @inheritdoc
     */
    public function getActivatedAt()
    {
        $completed = $this->activations->filter(function (Activation $activation) {
            return $activation->isCompleted();
        });

        if ($completed->isEmpty()) {
            return null;
        }

        return $completed->first()->getCompletedAt();
    }

    public static function getShortIdentifier(): string
    {
        return Str::slug(class_basename(static::class));
    }

    public static function getIdentifier(): string
    {
        return class_basename(static::class);
    }

    protected function createPermission($permission, $value)
    {
        return new DefaultUserPermission($this, $permission, $value);
    }

    /**
     * @inheritdoc
     */
    protected function makePermissionsInstance()
    {
        $permissionsFactory = $this->getPermissionsFactory();

        if (! is_callable($permissionsFactory)) {
            throw new \InvalidArgumentException('No PermissionFactory callable given. PermissionFactory callable should be set by the DoctrineUserRepository on instance creation. New instances will use a NullPermissions implementation until persisted.');
        }

        $secondary = $this->roles->map(function (Permissible $role) {
            return $role->getPermissions();
        });

        return $permissionsFactory($this->permissions, $secondary->getValues());
    }

    /**
     * @param  string  $username
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    private function changeUsername(string $username): void
    {
        $username = strtolower(trim($username));

        if (strlen($username) < 1) {
            throw new \InvalidArgumentException('Username cannot be empty');
        }

        $this->username = $username;
    }

    private function changePassword(string $password, ?Carbon $expiration = null): void
    {
        $this->password = new ValueObjects\Password($password);
        $this->setPasswordExpiration($expiration);
    }

    public function setPersistableKey(string $key)
    {
        return 'user_id';
    }

    public function setPersistableRelationship(string $persistableRelationship)
    {
        return 'persistences';
    }
}
