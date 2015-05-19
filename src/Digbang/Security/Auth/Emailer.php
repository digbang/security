<?php namespace Digbang\Security\Auth;

use Cartalyst\Sentry\Users\UserInterface;
use Illuminate\Config\Repository;
use Illuminate\Mail\Mailer;
use Illuminate\Mail\Message;

class Emailer
{
	/**
	 * @var \Illuminate\Mail\Mailer
	 */
	protected $mailer;

	/**
	 * @var \Illuminate\Config\Repository
	 */
	protected $config;

	public function __construct(Mailer $mailer, Repository $config)
	{
		$this->mailer = $mailer;
		$this->config = $config;
	}

	/**
	 * @param UserInterface $user
	 * @param string        $link
	 */
	public function sendPasswordReset(UserInterface $user, $link)
    {
	    $this->send('security::emails.reset-password', $user, $link, $this->config->get(
		    'security::emails.password-reset.subject'
	    ));
    }

	/**
	 * @param UserInterface $user
	 * @param string        $link
	 */
    public function sendActivation(UserInterface $user, $link)
    {
	    $this->send('security::emails.activation', $user, $link, $this->config->get(
		    'security::emails.activation.subject'
	    ));
    }

	/**
	 * @param string        $view
	 * @param UserInterface $user
	 * @param string        $link
	 * @param string        $subject
	 */
	protected function send($view, UserInterface $user, $link, $subject)
	{
		$from = $this->config->get('security::emails.from');

		$this->mailer->send(
			$view,
			[
				'name' => $user->getLoginName(),
				'link' => $link
			],
			function (Message $message) use ($user, $from, $subject)
			{
				$message->from($from);
				$message->to($user->getLogin(), $user->getLoginName());
				$message->subject($subject);
			}
		);
	}
}
