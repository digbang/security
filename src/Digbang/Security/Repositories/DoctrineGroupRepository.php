<?php namespace Digbang\Security\Repositories;

use Cartalyst\Sentry\Groups\GroupNotFoundException;
use Cartalyst\Sentry\Groups\ProviderInterface as GroupProviderInterface;
use Digbang\Security\Contracts\Group;
use Digbang\Security\Contracts\RepositoryAware;
use Digbang\Security\Entities\Group as DefaultGroup;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Illuminate\Config\Repository;

class DoctrineGroupRepository extends EntityRepository implements GroupProviderInterface
{
	private $entityName;

	/**
	 * @param EntityManagerInterface        $em
	 * @param \Illuminate\Config\Repository $config
	 */
    public function __construct(EntityManagerInterface $em, Repository $config)
    {
        parent::__construct($em, $em->getClassMetadata(
	        $this->entityName = $config->get('security::auth.groups.model', DefaultGroup::class)
        ));
    }

    /**
     * Find the group by ID.
     *
     * @param  int $id
     *
     * @return Group $group
     * @throws \Cartalyst\Sentry\Groups\GroupNotFoundException
     */
    public function findById($id)
    {
        $group = $this->find($id);

        if (!$group)
        {
            throw new GroupNotFoundException("Group $id not found.");
        }

        return $this->group($group);
    }

    /**
     * Find the group by name.
     *
     * @param  string $name
     *
     * @return Group  $group
     * @throws \Cartalyst\Sentry\Groups\GroupNotFoundException
     */
    public function findByName($name)
    {
        $group = $this->findOneBy([
            'name' => $name
        ]);

        if (!$group)
        {
            throw new GroupNotFoundException("Group $name not found.");
        }

        return $this->group($group);
    }

    /**
     * Creates a group.
     *
     * @param  array $attributes
     *
     * @return Group
     */
    public function create(array $attributes)
    {
	    $entityName = $this->entityName;

        $group = $entityName::create($attributes['name'], array_get($attributes, 'permissions', []));

        $this->save($group);

        return $this->group($group);
    }

    /**
     * @param Group $group
     */
    public function save(Group $group)
    {
        $em = $this->getEntityManager();

        $em->persist($group);
        $em->flush();
    }

    /**
     * @param Group $group
     */
    public function delete(Group $group)
    {
        $em = $this->getEntityManager();

        $em->remove($group);
        $em->flush();
    }

    /**
     * @param Group $group
     *
     * @return Group
     */
    private function group(Group $group)
    {
	    if ($group instanceof RepositoryAware)
	    {
		    $group->setRepository($this);
	    }

        return $group;
    }

	/**
	 * @param string $name
	 * @param string $permission
	 * @param string $orderBy
	 * @param string $orderSense
	 * @param int    $limit
	 * @param int    $offset
	 *
	 * @return Paginator
	 * @throws Mapping\MappingException
	 * @throws \Doctrine\ORM\Query\QueryException
	 */
	public function search($name = null, $permission = null, $orderBy = null, $orderSense = 'asc', $limit = 10, $offset = 0)
	{
		$queryBuilder = $this->createQueryBuilder('g');
		$expressionBuilder = Criteria::expr();

		$filters = [];

		if ($name)
		{
			$filters[] = $expressionBuilder->contains('name', $name);
		}

		$criteria = Criteria::create();

		if (!empty($filters))
		{
			$criteria->where($expressionBuilder->andX(...$filters));
		}

		if ($orderBy && $orderSense)
		{
			$criteria->orderBy([$orderBy => $orderSense]);
		}

		$criteria->setMaxResults($limit);
		$criteria->setFirstResult($offset);

		$queryBuilder->addCriteria($criteria);

		if ($permission !== null)
		{
			$permissionClass = $this->getClassMetadata()->getAssociationMapping('permissions')['targetEntity'];
			$queryBuilder->andWhere($queryBuilder->expr()->exists(
				"SELECT 1 FROM $permissionClass p WHERE p.permission LIKE :permission AND p.group = g.id"
			));

			$queryBuilder->setParameter('permission', "%$permission%");
		}

		return new Paginator($queryBuilder->getQuery());
	}
}
