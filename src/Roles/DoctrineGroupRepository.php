<?php namespace Digbang\Security\Repositories;

use Cartalyst\Sentinel\Roles\RoleRepositoryInterface;
use Digbang\Security\Contracts\Role;
use Digbang\Security\Contracts\RepositoryAware;
use Digbang\Security\Entities\Role as DefaultGroup;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping;
use Illuminate\Contracts\Config\Repository;

class DoctrineGroupRepository extends EntityRepository implements RoleRepositoryInterface
{
	private $entityName;

	/**
	 * @param EntityManager $entityManager
	 * @param Repository    $config
	 */
    public function __construct(EntityManager $entityManager, Repository $config)
    {
        parent::__construct($entityManager, $entityManager->getClassMetadata(
	        $this->entityName = $config->get('security::auth.groups.model', DefaultGroup::class)
        ));
    }

    /**
     * Find the group by ID.
     *
     * @param  int $id
     *
     * @return Role $group
     * @throws \InvalidArgumentException
     */
    public function findById($id)
    {
        /** @type Role $group */
        $group = $this->find($id);

        if (!$group)
        {
            throw new \InvalidArgumentException("Group $id not found.");
        }

        return $this->group($group);
    }

    /**
     * Finds a role by the given slug.
     *
     * @param  string $slug
     *
*@return Role
     */
    public function findBySlug($slug)
    {
        /** @type Role $group */
        $group = $this->findOneBy(['slug' => $slug]);

        if (!$group)
        {
            throw new \InvalidArgumentException("Group $slug not found.");
        }

        return $this->group($group);
    }


    /**
     * Find the group by name.
     *
     * @param  string $name
     *
     * @return Role  $group
     * @throws \InvalidArgumentException
     */
    public function findByName($name)
    {
        /** @type Role $group */
        $group = $this->findOneBy(['name' => $name]);

        if (!$group)
        {
            throw new \InvalidArgumentException("Group $name not found.");
        }

        return $this->group($group);
    }

    /**
     * Creates a group.
     *
     * @param  array $attributes
     *
     * @return Role
     */
    public function create(array $attributes)
    {
	    $entityName = $this->entityName;

        $group = $entityName::create($attributes['name'], array_get($attributes, 'permissions', []));

        $this->save($group);

        return $this->group($group);
    }

    /**
     * @param Role $group
     */
    public function save(Role $group)
    {
        $entityManager = $this->getEntityManager();

        $entityManager->persist($group);
        $entityManager->flush();
    }

    /**
     * @param Role $group
     */
    public function delete(Role $group)
    {
        $entityManager = $this->getEntityManager();

        $entityManager->remove($group);
        $entityManager->flush();
    }

    /**
     * @param Role $group
     *
     * @return Role
     */
    private function group(Role $group)
    {
	    if ($group instanceof RepositoryAware)
	    {
		    $group->setRepository($this);
	    }

        return $group;
    }

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

		return $queryBuilder->getQuery()->getResult();
	}
}
