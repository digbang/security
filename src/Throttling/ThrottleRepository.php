<?php

namespace Digbang\Security\Throttling;

use Cartalyst\Sentinel\Throttling\ThrottleRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;

interface ThrottleRepository extends ObjectRepository, ThrottleRepositoryInterface
{
    /**
     * Sets the global interval.
     *
     * @param  int  $globalInterval
     */
    public function setGlobalInterval($globalInterval);

    /**
     * Sets the global thresholds.
     *
     * @param  int|array  $globalThresholds
     */
    public function setGlobalThresholds($globalThresholds);

    /**
     * Sets the IP address interval.
     *
     * @param  int  $ipInterval
     */
    public function setIpInterval($ipInterval);

    /**
     * Sets the IP address thresholds.
     *
     * @param  int|array  $ipThresholds
     */
    public function setIpThresholds($ipThresholds);

    /**
     * Sets the user interval.
     *
     * @param  int  $userInterval
     */
    public function setUserInterval($userInterval);

    /**
     * Sets the user thresholds.
     *
     * @param  int|array  $userThresholds
     */
    public function setUserThresholds($userThresholds);
}
