<?php namespace Digbang\Security\Throttling;

use Carbon\Carbon;
use Cartalyst\Sentinel\Users\UserInterface;
use Digbang\Security\Users\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping;
use Illuminate\Support\Collection;

abstract class DoctrineThrottleRepository extends EntityRepository implements ThrottleRepository
{
    /**
     * The interval which failed logins are checked, to prevent brute force.
     *
     * @var int
     */
    protected $globalInterval = 900;

    /**
     * The global thresholds configuration array.
     *
     * If an array is set, the key is the number of failed login attemps
     * and the value is the delay in seconds before another login can
     * occur.
     *
     * If an integer is set, it represents the number of attempts
     * before throttling locks out in the current interval.
     *
     * @var int|array
     */
    protected $globalThresholds = [
        10 => 1,
        20 => 2,
        30 => 4,
        40 => 8,
        50 => 16,
        60 => 32,
    ];

    /**
     * Cached global throttles collection within the interval.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $globalThrottles;

    /**
     * The interval at which point one IP address' failed logins are checked.
     *
     * @var int
     */
    protected $ipInterval = 900;

    /**
     * Works identical to global thresholds, except specific to an IP address.
     *
     * @var int|array
     */
    protected $ipThresholds = 5;

    /**
     * The cached IP address throttle collections within the interval.
     *
     * @var Collection
     */
    protected $ipThrottles;

    /**
     * The interval at which point failed logins for one user are checked.
     *
     * @var int
     */
    protected $userInterval = 900;

    /**
     * Works identical to global and IP address thresholds, regarding a user.
     *
     * @var int|array
     */
    protected $userThresholds = 5;

    /**
     * The cached user throttle collections within the interval.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $userThrottles;

	/**
	 * @param EntityManager $entityManager
	 */
    public function __construct(EntityManager $entityManager)
    {
        parent::__construct($entityManager, $entityManager->getClassMetadata(
	        $this->entityName()
        ));

        $this->ipThrottles   = new Collection;
        $this->userThrottles = new Collection;
    }

    /**
     * Get the FQCN of each Throttle type:
     *   - null:     Base throttle type (eg: Digbang\Security\Throttling\DefaultThrottle)
     *   - 'global': Global throttle type (eg: Digbang\Security\Throttling\DefaultGlobalThrottle)
     *   - 'ip':     Ip throttle type (eg: Digbang\Security\Throttling\DefaultIpThrottle)
     *   - 'user':   User throttle type (eg: Digbang\Security\Throttling\DefaultUserThrottle)
     *
     * @param string|null $type
     *
     * @return string
     */
    abstract protected function entityName($type = null);

	/**
	 * Create a GlobalThrottle object
	 * @return Throttle
	 */
	abstract protected function createGlobalThrottle();

	/**
	 * Create an IpThrottle object
	 *
	 * @param string $ipAddress
	 * @return Throttle
	 */
	abstract protected function createIpThrottle($ipAddress);

	/**
	 * Create a UserThrottle object
	 *
	 * @param User $user
	 * @return Throttle
	 */
	abstract protected function createUserThrottle(User $user);

    /**
     * Sets the global interval.
     *
     * @param  int  $globalInterval
     * @return void
     */
    public function setGlobalInterval($globalInterval)
    {
        $this->globalInterval = (int) $globalInterval;
    }

    /**
     * Sets the global thresholds.
     *
     * @param  int|array  $globalThresholds
     * @return void
     */
    public function setGlobalThresholds($globalThresholds)
    {
        $this->globalThresholds = is_array($globalThresholds) ? $globalThresholds : (int) $globalThresholds;
    }

    /**
     * Sets the IP address interval.
     *
     * @param  int  $ipInterval
     * @return void
     */
    public function setIpInterval($ipInterval)
    {
        $this->ipInterval = (int) $ipInterval;
    }

    /**
     * Sets the IP address thresholds.
     *
     * @param  int|array  $ipThresholds
     * @return void
     */
    public function setIpThresholds($ipThresholds)
    {
        $this->ipThresholds = is_array($ipThresholds) ? $ipThresholds : (int) $ipThresholds;
    }

    /**
     * Sets the user interval.
     *
     * @param  int  $userInterval
     * @return void
     */
    public function setUserInterval($userInterval)
    {
        $this->userInterval = (int) $userInterval;
    }

    /**
     * Sets the user thresholds.
     *
     * @param  int|array  $userThresholds
     * @return void
     */
    public function setUserThresholds($userThresholds)
    {
        $this->userThresholds = is_array($userThresholds) ? $userThresholds : (int) $userThresholds;
    }

    /**
     * {@inheritDoc}
     */
    public function globalDelay()
    {
        return $this->delay('global');
    }

    /**
     * {@inheritDoc}
     */
    public function ipDelay($ipAddress)
    {
        return $this->delay('ip', $ipAddress);
    }

    /**
     * {@inheritDoc}
     */
    public function userDelay(UserInterface $user)
    {
        return $this->delay('user', $user);
    }

	/**
	 * @param string|null $ipAddress
	 * @param User|null   $user
	 */
    public function log($ipAddress = null, UserInterface $user = null)
    {
        $throttles = [
            $this->createGlobalThrottle()
        ];

        if ($ipAddress !== null)
        {
            $throttles[] = $this->createIpThrottle($ipAddress);
        }

        if ($user !== null)
        {
            $throttles[] = $this->createUserThrottle($user);
        }

        $this->bulkSave($throttles);
    }

    /**
     * Returns a delay for the given type.
     *
     * @param  string  $type
     * @param  mixed  $argument
     * @return int
     */
    protected function delay($type, $argument = null)
    {
        // Based on the given type, we will generate method and property names
        $method = 'get'.studly_case($type).'Throttles';

        $thresholds = $type.'Thresholds';

        /** @var Collection $throttles */
        $throttles = $this->{$method}($argument);

        if (! $throttles->count())
        {
            return 0;
        }

        if (is_array($this->$thresholds))
        {
            // Great, now we compare our delay against the most recent attempt

            /** @var DefaultThrottle $last */
            $last = $throttles->last();

            foreach (array_reverse($this->$thresholds, true) as $attempts => $delay)
            {
                if ($throttles->count() <= $attempts)
                {
                    continue;
                }

                if ($last->getCreatedAt()->diffInSeconds() < $delay)
                {
                    return $this->secondsToFree($last, $delay);
                }
            }
        }
        elseif ($throttles->count() > $this->$thresholds)
        {
            $interval = $type.'Interval';

            $first = $throttles->first();

            return $this->secondsToFree($first, $this->{$interval});
        }

        return 0;
    }

    /**
     * Returns the global throttles collection.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getGlobalThrottles()
    {
        if ($this->globalThrottles === null)
        {
            $this->globalThrottles = $this->loadGlobalThrottles();
        }

        return $this->globalThrottles;
    }

    /**
     * Loads and returns the global throttles collection.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function loadGlobalThrottles()
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
	        ->select('t')
            ->from($this->entityName('global'), 't')
            ->where('t.createdAt > :interval')
            ->setParameter('interval', Carbon::now()->subSeconds($this->globalInterval));

        return new Collection($queryBuilder->getQuery()->getResult());
    }

    /**
     * Returns the IP address throttles collection.
     *
     * @param  string  $ipAddress
     * @return \Illuminate\Support\Collection
     */
    protected function getIpThrottles($ipAddress)
    {
        if (! $this->ipThrottles->has($ipAddress))
        {
            $this->ipThrottles[$ipAddress] = $this->loadIpThrottles($ipAddress);
        }

        return $this->ipThrottles[$ipAddress];
    }

    /**
     * Loads and returns the IP address throttles collection.
     *
     * @param  string  $ipAddress
     * @return \Illuminate\Support\Collection
     */
    protected function loadIpThrottles($ipAddress)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder
	        ->select('t')
	        ->from($this->entityName('ip'), 't')
            ->where('t.createdAt > :interval')
            ->andWhere('t.ip = :ip')
            ->setParameters([
                'interval' => Carbon::now()->subSeconds($this->ipInterval),
                'ip'  => $ipAddress
            ]);

        return new Collection($queryBuilder->getQuery()->getResult());
    }

    /**
     * Returns the user throttles collection.
     *
     * @param  \Cartalyst\Sentinel\Users\UserInterface  $user
     * @return \Illuminate\Support\Collection
     */
    protected function getUserThrottles(UserInterface $user)
    {
	    if (! $this->userThrottles->has($user->getUserId()))
        {
            $this->userThrottles[$user->getUserId()] = $this->loadUserThrottles($user);
        }

        return $this->userThrottles[$user->getUserId()];
    }

    /**
     * Loads and returns the user throttles collection.
     *
     * @param  \Cartalyst\Sentinel\Users\UserInterface  $user
     * @return \Illuminate\Support\Collection
     */
    protected function loadUserThrottles(UserInterface $user)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
	        ->select('t')
	        ->from($this->entityName('user'), 't')
            ->where('t.createdAt > :interval')
            ->andWhere('t.user = :user')
            ->setParameters([
                'interval' => Carbon::now()->subSeconds($this->userInterval),
                'user'     => $user
            ]);

        return new Collection($queryBuilder->getQuery()->getResult());
    }

    /**
     * Returns the seconds to free based on the given throttle and
     * the presented delay in seconds, by comparing it to now.
     *
     * @param  DefaultThrottle $throttle
     * @param  int       $interval
     *
     * @return int
     */
    protected function secondsToFree(DefaultThrottle $throttle, $interval)
    {
        return $throttle->getCreatedAt()->addSeconds($interval)->diffInSeconds();
    }

	/**
	 * Persist an array of Throttles and flush them together.
	 *
	 * @param array $throttles
	 */
    protected function bulkSave(array $throttles)
    {
        $entityManager = $this->getEntityManager();

        foreach ($throttles as $throttle)
        {
            $entityManager->persist($throttle);
        }

        $entityManager->flush();
    }
}
