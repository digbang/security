<?php

namespace Digbang\Security\Activations;

/**
 * Interface Activation.
 *
 * @property string $code
 */
interface Activation
{
    /**
     * Sentinel 2.0.6 will access $activation->code, so this must
     * be implemented for now.
     *
     * @param  string  $name
     * @return string
     */
    public function __get($name);

    public function complete();

    /**
     * Get the unique code associated with this activation.
     *
     * @return string
     */
    public function getCode();

    /**
     * @return \Carbon\Carbon
     */
    public function getCreatedAt();

    /**
     * @return bool
     */
    public function isCompleted();

    /**
     * @return \Carbon\Carbon
     */
    public function getCompletedAt();
}
