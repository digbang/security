<?php

namespace Digbang\Security\Users\ValueObjects;

class Name
{
    /**
     * @var string
     */
    private $firstName;

    /**
     * @var string
     */
    private $lastName;

    /**
     * Name constructor.
     *
     * @param  null|string  $firstName
     * @param  null|string  $lastName
     */
    public function __construct(?string $firstName = null, ?string $lastName = null)
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
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
        return $this->getFullName();
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param  string  $separator
     * @return string
     */
    public function getFullName($separator = ' ')
    {
        return $this->firstName.$separator.$this->lastName;
    }
}
