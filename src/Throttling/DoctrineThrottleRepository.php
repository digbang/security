<?php namespace Digbang\Security\Throttling;

use Carbon\Carbon;
use Cartalyst\Sentinel\Throttling\ThrottleRepositoryInterface;
use Cartalyst\Sentinel\Users\UserInterface;
use Digbang\Security\Contracts\Factories\ThrottleFactory;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\Collection;

class DoctrineThrottleRepository extends EntityRepository implements ThrottleRepositoryInterface
{
	/**
	 * @type string
	 */
	private $entityName;
    /**
     * @type Repository
     */
    private $config;

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
	 * @type ThrottleFactory
	 */
	private $throttleFactory;

	/**
	 * @param EntityManager   $entityManager
	 * @param Repository      $config
	 * @param ThrottleFactory $throttleFactory
	 */
    public function __construct(EntityManager $entityManager, Repository $config, ThrottleFactory $throttleFactory)
    {
        parent::__construct($entityManager, $entityManager->getClassMetadata(
	        $this->entityName = $config->get('digbang.security.auth.throttling.model', Throttle::class)
        ));

        $this->config          = $config;
	    $this->throttleFactory = $throttleFactory;
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
     * {@inheritDoc}
     */
    public function log($ipAddress = null, UserInterface $user = null)
    {
        $throttles = [
            $this->throttleFactory->createGlobalThrottle()
        ];

        if ($ipAddress !== null)
        {
            $throttles[] = $this->throttleFactory->createIpThrottle($ipAddress);
        }

        if ($user !== null) {
            $throttles[] = $this->throttleFactory->createUserThrottle($user);
        }

        $this->bulkSave($throttles);
    }

    /**
     * Returns the global interval.
     *
     * @return int
     */
    public function getGlobalInterval()
    {
        return $this->globalInterval;
    }

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
     * Returns the global thresholds.
     *
     * @return int|array
     */
    public function getGlobalThresholds()
    {
        return $this->globalThresholds;
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
     * Returns the IP address interval.
     *
     * @return int
     */
    public function getIpInterval()
    {
        return $this->ipInterval;
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
     * Returns the IP address thresholds.
     *
     * @return int|array
     */
    public function getIpThresholds()
    {
        return $this->ipThresholds;
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
     * Returns the user interval.
     *
     * @return int
     */
    public function getUserInterval()
    {
        return $this->userInterval;
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
     * Returns the user thresholds.
     *
     * @return int|array
     */
    public function getUserThresholds()
    {
        return $this->userThresholds;
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

        /** @type Collection $throttles */
        $throttles = $this->{$method}($argument);

        if (! $throttles->count()) {
            return 0;
        }

        if (is_array($this->$thresholds)) {
            // Great, now we compare our delay against the most recent attempt

            /** @type Throttle $last */
            $last = $throttles->last();

            foreach (array_reverse($this->$thresholds, true) as $attempts => $delay) {
                if ($throttles->count() <= $attempts) {
                    continue;
                }

                if ($last->getCreatedAt()->diffInSeconds() < $delay) {
                    return $this->secondsToFree($last, $delay);
                }
            }
        } elseif ($throttles->count() > $this->$thresholds) {
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
        if ($this->globalThrottles === null) {
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
        $interval = Carbon::now()
            ->subSeconds($this->globalInterval);

        $globalRepository = $this->getEntityManager()->getRepository(GlobalThrottle::class);

        $queryBuilder = $globalRepository->createQueryBuilder('t');
        $queryBuilder
            ->where('createdAt > :interval')
            ->setParameter('interval', $interval);

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
        $interval = Carbon::now()
            ->subSeconds($this->ipInterval);

        $ipRepository = $this->getEntityManager()->getRepository(IpThrottle::class);

        $queryBuilder = $ipRepository->createQueryBuilder('t');
        $queryBuilder
            ->where('createdAt > :interval')
            ->andWhere('ip = :ip')
            ->setParameters([
                'interval' => $interval,
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
        $userId = $user->getUserId();

        if (! $this->userThrottles->has($userId))
        {
            $this->userThrottles[$userId] = $this->loadUserThrottles($user);
        }

        return $this->userThrottles[$userId];
    }

    /**
     * Loads and returns the user throttles collection.
     *
     * @param  \Cartalyst\Sentinel\Users\UserInterface  $user
     * @return \Illuminate\Support\Collection
     */
    protected function loadUserThrottles(UserInterface $user)
    {
        $interval = Carbon::now()
            ->subSeconds($this->userInterval);

        $ipRepository = $this->getEntityManager()->getRepository(UserThrottle::class);

        $queryBuilder = $ipRepository->createQueryBuilder('t');
        $queryBuilder
            ->where('createdAt > :interval')
            ->andWhere('user = :user')
            ->setParameters([
                'interval' => $interval,
                'user'     => $user
            ]);

        return new Collection($queryBuilder->getQuery()->getResult());
    }

    /**
     * Returns the seconds to free based on the given throttle and
     * the presented delay in seconds, by comparing it to now.
     *
     * @param  Throttle  $throttle
     * @param  int       $interval
     * @return int
     */
    protected function secondsToFree(Throttle $throttle, $interval)
    {
        return $throttle->getCreatedAt()->addSeconds($interval)->diffInSeconds();
    }

    private function bulkSave(array $throttles)
    {
        $entityManager = $this->getEntityManager();

        foreach ($throttles as $throttle)
        {
            $entityManager->persist($throttle);
        }

        $entityManager->flush();
    }
}
