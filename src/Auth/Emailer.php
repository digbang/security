<?php namespace Digbang\Security\Auth;

use Digbang\Security\Contracts\User;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Mail\Message;

class Emailer
{
	/**
	 * @type Mailer
	 */
	protected $mailer;

	/**
	 * @type Repository
	 */
	protected $config;

	/**
	 * @param Mailer     $mailer
	 * @param Repository $config
	 */
	public function __construct(Mailer $mailer, Repository $config)
	{
		$this->mailer = $mailer;
		$this->config = $config;
	}

	/**
	 * @param User   $user
	 * @param string $link
	 */
	public function sendPasswordReset(User $user, $link)
    {
	    $this->send('security::emails.reset-password', $user, $link, $this->config->get(
		    'security::emails.password-reset.subject'
	    ));
    }

	/**
	 * @param User   $user
	 * @param string $link
	 */
    public function sendActivation(User $user, $link)
    {
	    $this->send('security::emails.activation', $user, $link, $this->config->get(
		    'security::emails.activation.subject'
	    ));
    }

	/**
	 * @param string $view
	 * @param User   $user
	 * @param string $link
	 * @param string $subject
	 */
	protected function send($view, User $user, $link, $subject)
	{
		$from = $this->getSenderConfiguration();

		$this->mailer->send($view, compact('user', 'link'), $this->makeMessageCallback($user, $from, $subject));
	}

	/**
	 * @param User   $user
	 * @param array  $from
	 * @param string $subject
	 *
	 * @return \Closure
	 */
	protected function makeMessageCallback(User $user, array $from, $subject)
	{
		return function (Message $message) use ($user, $from, $subject) {
			$message->from($from['address'], $from['name']);
			$message->to($user->getEmail(), $user->getFirstName() ?: $user->getUserLogin());
			$message->subject($subject);
		};
	}

	/**
	 * Parse the configuration and return an array with 'address' and 'name' keys.
	 * @return array
	 */
	protected function getSenderConfiguration()
	{
		$config = $this->config->get('security::emails.from');

		if (! array_key_exists('address', $config))
		{
			throw new \InvalidArgumentException("Email configuration requires an 'address' to be set.");
		}

		if (! array_key_exists('name', $config))
		{
			$config['name'] = $config['address'];
		}

		return $config;
	}
}
