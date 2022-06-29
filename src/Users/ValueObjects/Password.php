<?php

namespace Digbang\Security\Users\ValueObjects;

class Password
{
    /**
     * @var string
     */
    private $hash;

    /**
     * Password constructor.
     *
     * @param  string  $plain
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(string $plain)
    {
        if (strlen(trim($plain)) < 1) {
            throw new \InvalidArgumentException('Password cannot be empty');
        }

        $this->hash = password_hash($plain, PASSWORD_DEFAULT);
    }

    /**
     * The __toString method allows a class to decide how it will react when it is converted to a string.
     *
     * @return string
     *
     * @see http://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.tostring
     */
    public function __toString()
    {
        return '******';
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @param  string  $password
     * @return bool
     */
    public function check(string $password): bool
    {
        return password_verify($password, $this->getHash());
    }
}
