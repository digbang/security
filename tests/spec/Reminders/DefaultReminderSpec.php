<?php

namespace spec\Digbang\Security\Reminders;

use Carbon\Carbon;
use Digbang\Security\Reminders\Reminder;
use Digbang\Security\Users\User;
use PhpSpec\ObjectBehavior;

/**
 * Class DefaultReminderSpec.
 *
 * @mixin \Digbang\Security\Reminders\DefaultReminder
 */
class DefaultReminderSpec extends ObjectBehavior
{
    public function let(User $user)
    {
        $this->beConstructedWith($user);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Digbang\Security\Reminders\DefaultReminder');
    }

    public function it_is_a_reminder()
    {
        $this->shouldHaveType(Reminder::class);
    }

    public function it_should_complete_itself()
    {
        Carbon::setTestNow($now = Carbon::now());
        $this->isCompleted()->shouldBe(false);
        $this->getCompletedAt()->shouldBe(null);

        $this->complete();

        $this->isCompleted()->shouldBe(true);
        $this->getCompletedAt()->shouldBeLike($now);
    }
}
