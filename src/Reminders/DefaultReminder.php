<?php

namespace Digbang\Security\Reminders;

use Carbon\Carbon;
use Digbang\Security\Support\TimestampsTrait;
use Digbang\Security\Users\User;
use Illuminate\Support\Str;

class DefaultReminder implements Reminder
{
    use TimestampsTrait;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var string
     */
    protected $code;

    /**
     * @var bool
     */
    protected $completed = false;

    /**
     * @var Carbon
     */
    protected $completedAt;

    /**
     * DefaultReminder constructor.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->code = Str::random(32);
    }

    /**
     * {@inheritdoc}
     */
    public function complete()
    {
        $this->completed = true;
        $this->completedAt = Carbon::now();
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return bool
     */
    public function isCompleted()
    {
        return $this->completed;
    }

    /**
     * @return Carbon
     */
    public function getCompletedAt()
    {
        return $this->completedAt;
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
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }
}
