<?php

namespace Digbang\Security\Support;

trait TimestampsTrait
{
    /**
     * @var \Carbon\Carbon
     */
    protected $createdAt;

    /**
     * @var \Carbon\Carbon
     */
    protected $updatedAt;

    public function onPrePersist()
    {
        $now = new \Carbon\Carbon();

        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public function onPreUpdate()
    {
        $now = new \Carbon\Carbon();

        $this->updatedAt = $now;
    }
}
