<?php namespace spec\Digbang\Security\Auth;

use Cartalyst\Sentry\Sentry;
use Cartalyst\Sentry\Users\UserInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class AccessControlSpec
 * @mixin \PhpSpec\Wrapper\Subject
 * @mixin \Digbang\Security\Auth\AccessControl
 * @package spec\Digbang\Security\Auth
 */
class AccessControlSpec extends ObjectBehavior
{
    function it_is_initializable(Sentry $sentry)
    {
	    $this->beConstructedWith($sentry);
        $this->shouldHaveType('Digbang\Security\Auth\AccessControl');
    }

	function it_should_authenticate_users(Sentry $sentry, UserInterface $user)
	{
		$sentry->authenticate(Argument::cetera())
			->shouldBeCalled()
			->willReturn($user);
		$this->beConstructedWith($sentry);

		$this->authenticate('a.random@email.com', '1234')
			->shouldBeAnInstanceOf('Cartalyst\Sentry\Users\UserInterface');
	}

	function it_should_logout_the_current_user(Sentry $sentry)
	{
		$sentry->logout()->shouldBeCalled();
		$this->beConstructedWith($sentry);

		$this->logout();
	}

	function it_should_return_the_currently_logged_user(Sentry $sentry, UserInterface $user)
	{
		$sentry->getUser()->shouldBeCalled()->willReturn($user);
		$this->beConstructedWith($sentry);

		$this->getUser()
			->shouldBeAnInstanceOf('Cartalyst\Sentry\Users\UserInterface');
	}

	function it_should_return_false_when_there_is_no_currently_logged_user(Sentry $sentry)
	{
		$sentry->getUser()->shouldBeCalled()->willReturn(false);
		$this->beConstructedWith($sentry);

		$this->getUser()
			->shouldReturn(false);
	}

	function it_should_reset_a_password_for_a_given_password_token(Sentry $sentry, UserInterface $user)
	{
		$code = 'abcdef';
		$sentry->findUserByResetPasswordCode($code)->shouldBeCalled()->willReturn($user);

		$newPassword = '123456';
		$user->attemptResetPassword($code, $newPassword)->shouldBeCalled()->willReturn(true);

		$this->beConstructedWith($sentry);

		$this->resetPassword($code, $newPassword)->shouldReturn(true);
	}

	function it_shouldnt_reset_a_password_for_a_wrong_password_token(Sentry $sentry)
	{
		$code = 'abcdef';
		$sentry->findUserByResetPasswordCode($code)->shouldBeCalled()->willReturn(null);

		$this->beConstructedWith($sentry);

		$this->resetPassword($code, 'lalala')->shouldReturn(false);
	}

	function it_should_activate_users_for_a_given_activation_code(Sentry $sentry, UserInterface $user)
	{
		$code = 'abcdef';
		$sentry->findUserByActivationCode($code)->shouldBeCalled()->willReturn($user);

		$user->attemptActivation($code)->shouldBeCalled()->willReturn(true);
		$this->beConstructedWith($sentry);

		$this->activate($code)
			->shouldReturn(true);
	}

	function it_shouldnt_activate_users_for_a_wrong_activation_code(Sentry $sentry)
	{
		$code = 'abcdef';
		$sentry->findUserByActivationCode($code)->shouldBeCalled()->willReturn(null);

		$this->beConstructedWith($sentry);

		$this->activate($code)
			->shouldReturn(false);
	}

	function it_should_know_if_a_user_is_logged(Sentry $sentry)
	{
		$sentry->check()->shouldBeCalled()->willReturn(true);
		$this->beConstructedWith($sentry);

		$this->isLogged()->shouldReturn(true);
	}

	function it_should_know_if_a_user_isnt_logged(Sentry $sentry)
	{
		$sentry->check()->shouldBeCalled()->willReturn(false);
		$this->beConstructedWith($sentry);

		$this->isLogged()->shouldReturn(false);
	}
}
