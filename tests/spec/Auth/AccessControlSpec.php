<?php namespace spec\Digbang\Security\Auth;

use Cartalyst\Sentinel\Activations\ActivationRepositoryInterface;
use Cartalyst\Sentinel\Reminders\ReminderRepositoryInterface;
use Cartalyst\Sentinel\Sentinel;
use Cartalyst\Sentinel\Users\UserRepositoryInterface;
use Digbang\Security\Contracts\User;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class AccessControlSpec
 * @mixin \Digbang\Security\Auth\AccessControl
 * @package spec\Digbang\Security\Auth
 */
class AccessControlSpec extends ObjectBehavior
{
	function let(Sentinel $sentinel, ReminderRepositoryInterface $reminderRepository, ActivationRepositoryInterface $activationRepository, UserRepositoryInterface $userRepository, User $user)
	{
		$this->beConstructedWith($sentinel);

		$sentinel->getReminderRepository()->willReturn($reminderRepository);
		$sentinel->getActivationRepository()->willReturn($activationRepository);
		$sentinel->getUserRepository()->willReturn($userRepository);
		$sentinel->getUser()->willReturn($user);
	}

    function it_is_initializable()
    {
        $this->shouldHaveType('Digbang\Security\Auth\AccessControl');
    }

	function it_should_authenticate_users(Sentinel $sentinel, User $user)
	{
		$sentinel->authenticate(Argument::cetera())
			->shouldBeCalled()
			->willReturn($user);

		$this->authenticate('a.random@email.com', '1234')
			->shouldBeAnInstanceOf('Cartalyst\Sentinel\Users\UserInterface');
	}

	function it_should_logout_the_current_user(Sentinel $sentinel)
	{
		$sentinel->logout()->shouldBeCalled();

		$this->logout();
	}

	function it_should_return_the_currently_logged_user(Sentinel $sentinel, User $user)
	{
		$sentinel->getUser()->shouldBeCalled();

		$this->getUser()
			->shouldBeAnInstanceOf('Cartalyst\Sentinel\Users\UserInterface');
	}

	function it_should_return_false_when_there_is_no_currently_logged_user(Sentinel $sentinel)
	{
		$sentinel->getUser()->shouldBeCalled()->willReturn(false);

		$this->getUser()->shouldReturn(false);
	}

	function it_should_reset_a_password_for_a_given_password_token(Sentinel $sentinel, User $user, ReminderRepositoryInterface $reminderRepository)
	{
		$code = 'abcdef';
		$newPassword = '123456';

		$sentinel->getReminderRepository()->shouldBeCalled();

		$reminderRepository->exists($user, $code)->shouldBeCalled()->willReturn(true);
		$reminderRepository->complete($user, $code, $newPassword)->shouldBeCalled()->willReturn(true);

		$this->resetPassword($user, $code, $newPassword)->shouldReturn(true);
	}

	function it_shouldnt_reset_a_password_for_a_wrong_password_token(Sentinel $sentinel, User $user, ReminderRepositoryInterface $reminderRepository)
	{
		$code = 'abcdef';

		$reminderRepository->exists($user, $code)->shouldBeCalled()->willReturn(false);

		$this->resetPassword($user, $code, 'lalala')->shouldReturn(false);
	}

	function it_should_activate_users_for_a_given_activation_code(Sentinel $sentinel, User $user, ActivationRepositoryInterface $activationRepository)
	{
		$code = 'abcdef';

		$sentinel->getActivationRepository()->shouldBeCalled();

		$activationRepository->exists($user, $code)->shouldBeCalled()->willReturn(true);
		$activationRepository->complete($user, $code)->shouldBeCalled()->willReturn(true);

		$this->activate($user, $code)->shouldReturn(true);
	}

	function it_shouldnt_activate_users_for_a_wrong_activation_code(Sentinel $sentinel, User $user, ActivationRepositoryInterface $activationRepository)
	{
		$code = 'abcdef';

		$sentinel->getActivationRepository()->shouldBeCalled();

		$activationRepository->exists($user, $code)->shouldBeCalled()->willReturn(false);

		$this->activate($user, $code)->shouldReturn(false);
	}

	function it_should_know_if_a_user_is_logged(Sentinel $sentinel)
	{
		$sentinel->check()->shouldBeCalled()->willReturn(true);

		$this->isLogged()->shouldReturn(true);
	}

	function it_should_know_if_a_user_isnt_logged(Sentinel $sentinel)
	{
		$sentinel->check()->shouldBeCalled()->willReturn(false);

		$this->isLogged()->shouldReturn(false);
	}

	function it_should_check_if_a_reset_password_code_is_valid_for_a_given_user(Sentinel $sentinel, User $user, UserRepositoryInterface $userRepository, ReminderRepositoryInterface $reminderRepository)
	{
		$id = 1;
		$resetCode = 'a.reset.code';

		$sentinel->getUserRepository()->shouldBeCalled();

		$userRepository->findById($id)->shouldBeCalled()->willReturn($user);
		$reminderRepository->exists($user, $resetCode)->shouldBeCalled()->willReturn(true);

		$this->checkResetPasswordCode($id, $resetCode)->shouldReturn(true);
	}

	function it_should_check_if_a_reset_password_code_is_invalid_for_a_given_user(Sentinel $sentinel, User $user, UserRepositoryInterface $userRepository, ReminderRepositoryInterface $reminderRepository)
	{
		$id = 1;
		$resetCode = 'a.reset.code';

		$sentinel->getUserRepository()->shouldBeCalled();

		$userRepository->findById($id)->shouldBeCalled()->willReturn($user);
		$reminderRepository->exists($user, $resetCode)->shouldBeCalled()->willReturn(false);

		$this->checkResetPasswordCode($id, $resetCode)->shouldReturn(false);
	}

	function it_should_squeak_if_a_reset_password_code_is_given_for_an_invalid_user(Sentinel $sentinel, User $user, UserRepositoryInterface $userRepository, ReminderRepositoryInterface $reminderRepository)
	{
		$id = 1;
		$resetCode = 'a.reset.code';

		$sentinel->getUserRepository()->shouldBeCalled();

		$userRepository->findById($id)->shouldBeCalled()->willReturn(null);
		$reminderRepository->exists($user, $resetCode)->shouldNotBeCalled();

		$this->shouldThrow(\InvalidArgumentException::class)
			->duringCheckResetPasswordCode($id, $resetCode);
	}
}
