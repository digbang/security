<?php namespace Digbang\Security\Auth;

use Cartalyst\Sentry\Sentry;

class AccessControl
{
	/**
	 * @var \Cartalyst\Sentry\Sentry
	 */
	protected $sentry;

	function __construct(Sentry $sentry)
	{
		$this->sentry = $sentry;
	}

	public function authenticate($email, $password, $remember = false)
    {
	    return $this->sentry->authenticate(['login' => $email, 'password' => $password], $remember);
    }

    public function logout()
    {
        $this->sentry->logout();
    }

    public function getUser()
    {
        return $this->sentry->getUser();
    }

    public function resetPassword($code, $newPassword)
    {
        if ($user = $this->sentry->findUserByResetPasswordCode($code))
        {
	        return $user->attemptResetPassword($code, $newPassword);
        }

	    return false;
    }

    public function activate($code)
    {
        if ($user = $this->sentry->findUserByActivationCode($code))
        {
	        return $user->attemptActivation($code);
        }

	    return false;
    }

    public function isLogged()
    {
        return $this->sentry->check();
    }

	public function checkResetPasswordCode($id, $resetCode)
	{
		if ($user = $this->sentry->findUserById($id))
		{
			return $user->checkResetPasswordCode($resetCode);
		}

		return false;
	}
}
