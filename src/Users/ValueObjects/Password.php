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
     * @param string $plain
     */
    public function __construct($plain)
    {
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
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @param string $password
     *
     * @return bool
     */
    public function check($password)
    {
        return password_verify($password, $this->hash);
    }
}
