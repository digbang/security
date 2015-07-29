<?php namespace Digbang\Security\Roles;

use Cartalyst\Sentinel\Roles\RoleRepositoryInterface;
use Digbang\Security\Contracts\Entities\Role as RoleInterface;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping;

abstract class DoctrineRoleRepository extends EntityRepository implements RoleRepositoryInterface
{
	/**
	 * @param EntityManager $entityManager
	 */
    public function __construct(EntityManager $entityManager)
    {
        parent::__construct($entityManager, $entityManager->getClassMetadata(
	        $this->entityName()
        ));
    }

    /**
     * Get the entity name for this repository.
     * This entity MUST implement \Digbang\Security\Entities\Contracts\Role
     *
     * @return string
     */
    abstract protected function entityName();

    /**
     * Find the role by ID.
     *
     * @param  int $id
     *
     * @return RoleInterface $role
     * @throws \InvalidArgumentException
     */
    public function findById($id)
    {
        /** @type RoleInterface $role */
        $role = $this->find($id);

        if (!$role)
        {
            throw new \InvalidArgumentException("Group $id not found.");
        }

        return $role;
    }

    /**
     * Finds a role by the given slug.
     *
     * @param  string $slug
     * @return RoleInterface
     */
    public function findBySlug($slug)
    {
        /** @type RoleInterface $role */
        $role = $this->findOneBy(['slug' => $slug]);

        if (!$role)
        {
            throw new \InvalidArgumentException("Group $slug not found.");
        }

        return $role;
    }


    /**
     * Find the role by name.
     *
     * @param  string $name
     *
     * @return RoleInterface  $role
     * @throws \InvalidArgumentException
     */
    public function findByName($name)
    {
        /** @type RoleInterface $role */
        $role = $this->findOneBy(['name' => $name]);

        if (!$role)
        {
            throw new \InvalidArgumentException("Group $name not found.");
        }

        return $role;
    }

    /**
     * @param RoleInterface $role
     */
    public function save(RoleInterface $role)
    {
        $entityManager = $this->getEntityManager();

        $entityManager->persist($role);
        $entityManager->flush();
    }

    /**
     * @param RoleInterface $role
     */
    public function delete(RoleInterface $role)
    {
        $entityManager = $this->getEntityManager();

        $entityManager->remove($role);
        $entityManager->flush();
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
				"SELECT 1 FROM $permissionClass p WHERE p.permission LIKE :permission AND p.role = g.id"
			));

			$queryBuilder->setParameter('permission', "%$permission%");
		}

		return $queryBuilder->getQuery()->getResult();
	}
}
