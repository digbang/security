<?php namespace Digbang\Security\Roles;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping;
use Illuminate\Support\Collection;

abstract class DoctrineRoleRepository extends EntityRepository implements RoleRepository
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
	 * @param string      $name
	 * @param string|null $slug
	 *
	 * @return Role
	 */
    abstract protected function createRole($name, $slug = null);

    /**
     * Find the role by ID.
     *
     * @param  int $id
     *
     * @return Role $role
     */
    public function findById($id)
    {
        return $this->find($id);
    }

    /**
     * Finds a role by the given slug.
     *
     * @param  string $slug
     * @return Role
     */
    public function findBySlug($slug)
    {
        return $this->findOneBy(['slug' => $slug]);

    }

    /**
     * Find the role by name.
     *
     * @param  string $name
     *
     * @return Role  $role
     * @throws
     */
    public function findByName($name)
    {
        return $this->findOneBy(['name' => $name]);
    }

    /**
     * {@inheritdoc}
     */
	public function create($name, $slug = null)
	{
		$role = $this->createRole($name, $slug);

		$this->save($role);

		return $role;
	}

    /**
     * {@inheritdoc}
     */
    public function save(Role $role)
    {
        $entityManager = $this->getEntityManager();

        $entityManager->persist($role);
        $entityManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Role $role)
    {
        $entityManager = $this->getEntityManager();

        $entityManager->remove($role);
        $entityManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        return new Collection(parent::findAll());
    }
}
