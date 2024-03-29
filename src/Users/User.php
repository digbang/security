<?php

namespace Digbang\Security\Users;

use Carbon\Carbon;
use Cartalyst\Sentinel\Persistences\PersistableInterface;
use Cartalyst\Sentinel\Users\UserInterface;

interface User extends UserInterface, PersistableInterface
{
    /**
     * @param  array  $credentials
     */
    public function update(array $credentials);

    /**
     * @param  string  $password
     * @return bool
     */
    public function checkPassword($password);

    public function recordLogin();

    public function hasExpiredPassword();

    /**
     * @return string
     */
    public function getEmail();

    /**
     * @return \Digbang\Security\Users\ValueObjects\Name|string
     */
    public function getName();

    /**
     * @return string
     */
    public function getUsername();

    /**
     * @return bool
     */
    public function isActivated();

    /**
     * @return \Carbon\Carbon
     */
    public function getLastLogin(): ?Carbon;

    /**
     * @return \Carbon\Carbon
     */
    public function getCreatedAt();

    /**
     * @return \Carbon\Carbon
     */
    public function getUpdatedAt();

    /**
     * @return \Carbon\Carbon|null
     */
    public function getActivatedAt();
}
