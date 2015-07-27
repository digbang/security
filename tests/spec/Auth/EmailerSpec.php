<?php namespace spec\Digbang\Security\Auth;

use Digbang\Security\Contracts\User;
use Illuminate\Config\Repository;
use Illuminate\Mail\Mailer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class EmailerSpec
 * @mixin \PhpSpec\Wrapper\Subject
 * @mixin \Digbang\Security\Auth\Emailer
 * @package spec\Digbang\Security\Auth
 */
class EmailerSpec extends ObjectBehavior
{
    function it_is_initializable(Mailer $mailer, Repository $config)
    {
	    $this->beConstructedWith($mailer, $config);

        $this->shouldHaveType('Digbang\Security\Auth\Emailer');
    }

	function it_should_email_an_activation_code_to_a_user(Mailer $mailer, User $user, Repository $config)
	{
		$mailer->send(Argument::any(), Argument::any(), Argument::any())
			->shouldBeCalled()->willReturn(null);

		$config->get('digbang.security.emails.activation.subject')->shouldBeCalled();
		$config->get('digbang.security.emails.from')->shouldBeCalled()->willReturn([
			'name' => 'Testing dude',
			'address' => 'unittests@example.com'
		]);

		$this->beConstructedWith($mailer, $config);

		$user->name = 'Some Username';
		$user->email = 'some@email.com';

		$this->sendActivation($user, 'http://an/activation/url');
	}

	function it_should_email_a_reset_password_link_to_a_user(Mailer $mailer, User $user, Repository $config)
	{
		$mailer->send(Argument::any(), Argument::any(), Argument::any())
			->shouldBeCalled()->willReturn(null);

		$config->get('digbang.security.emails.password-reset.subject')->shouldBeCalled();
		$config->get('digbang.security.emails.from')->shouldBeCalled()->willReturn([
			'name' => 'Testing dude',
			'address' => 'unittests@example.com'
		]);

		$this->beConstructedWith($mailer, $config);

		$user->name = 'Some Username';
		$user->email = 'some@email.com';

		$this->sendPasswordReset($user, 'http://the/password/reset/link');
	}
}
