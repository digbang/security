<?php namespace Digbang\Security\Auth;

use Cartalyst\Sentry\Users\UserInterface;
use Digbang\Security\Contracts\User;
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

		$name = $this->extractName($user);

		$this->mailer->send($view, compact('name', 'link'), function (Message $message) use ($user, $from, $subject, $name){
			$message->from($from['address'], $from['name']);
			$message->to($user->getLogin(), $name);
			$message->subject($subject);
		});
	}

	/**
	 * @param UserInterface $user
	 *
	 * @return string
	 */
	protected function extractName(UserInterface $user)
	{
		switch (true)
		{
			case $user instanceof User:
				return $user->getFirstName() ?: $user->getLogin();
			case isset($user->name):
				return $user->name;
			default:
				return $user->getLogin();
		}
	}
}
