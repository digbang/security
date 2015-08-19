<?php namespace Digbang\Security\Throttling;

use Cartalyst\Sentinel\Throttling\ThrottleRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;

interface ThrottleRepository extends ObjectRepository, ThrottleRepositoryInterface
{
	/**
     * Sets the global interval.
     *
     * @param  int  $globalInterval
     * @return void
     */
    public function setGlobalInterval($globalInterval);

    /**
     * Sets the global thresholds.
     *
     * @param  int|array  $globalThresholds
     * @return void
     */
    public function setGlobalThresholds($globalThresholds);

    /**
     * Sets the IP address interval.
     *
     * @param  int  $ipInterval
     * @return void
     */
    public function setIpInterval($ipInterval);

    /**
     * Sets the IP address thresholds.
     *
     * @param  int|array  $ipThresholds
     * @return void
     */
    public function setIpThresholds($ipThresholds);

    /**
     * Sets the user interval.
     *
     * @param  int  $userInterval
     * @return void
     */
    public function setUserInterval($userInterval);

    /**
     * Sets the user thresholds.
     *
     * @param  int|array  $userThresholds
     * @return void
     */
    public function setUserThresholds($userThresholds);
}
