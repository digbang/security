<?php namespace Digbang\Security\Auth;

use Cartalyst\Sentinel\Sentinel;
use Digbang\Security\Contracts\User;

class AccessControl
{
	/**
	 * @var Sentinel
	 */
	protected $sentinel;

    /**
     * @param Sentinel $sentinel
     */
	public function __construct(Sentinel $sentinel)
	{
		$this->sentinel = $sentinel;
	}

    /**
     * @param string     $email
     * @param string     $password
     * @param bool|false $remember
     *
     * @return bool|User
     */
	public function authenticate($email, $password, $remember = false)
    {
	    return $this->sentinel->authenticate(['login' => $email, 'password' => $password], $remember);
    }

    /**
     * @return void
     */
    public function logout()
    {
        $this->sentinel->logout();
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->sentinel->getUser();
    }

    /**
     * @param User   $user
     * @param string $code
     * @param string $newPassword
     *
     * @return bool
     */
    public function resetPassword(User $user, $code, $newPassword)
    {
        $reminderRepository = $this->sentinel->getReminderRepository();

        if ($reminderRepository->exists($user, $code))
        {
	        return $reminderRepository->complete($user, $code, $newPassword);
        }

	    return false;
    }

    /**
     * @param User   $user
     * @param string $code
     *
     * @return bool
     */
    public function activate(User $user, $code)
    {
        $activationRepository = $this->sentinel->getActivationRepository();

        if ($activationRepository->exists($user, $code))
        {
	        return $activationRepository->complete($user, $code);
        }

	    return false;
    }

    /**
     * @return bool
     */
    public function isLogged()
    {
        return $this->sentinel->check() !== false;
    }

    /**
     * @param int    $id
     * @param string $resetCode
     *
     * @return bool
     */
	public function checkResetPasswordCode($id, $resetCode)
	{
        $user = $this->sentinel->getUserRepository()->findById($id);

        if (! $user)
        {
            throw new \InvalidArgumentException("User id $id does not exist.");
        }

        return $this->sentinel->getReminderRepository()->exists($user, $resetCode);
	}
}
