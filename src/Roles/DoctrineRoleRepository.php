<?php

namespace Digbang\Security\Roles;

use Cartalyst\Sentinel\Roles\RoleInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Illuminate\Support\Collection;

abstract class DoctrineRoleRepository extends EntityRepository implements RoleRepository
{
    /**
     * @param  EntityManager  $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        parent::__construct($entityManager, $entityManager->getClassMetadata(
            $this->entityName()
        ));
    }

    /**
     * Find the role by ID.
     *
     * @param  int  $id
     * @return Role|RoleInterface $role
     */
    public function findById(int $id): ?RoleInterface
    {
        /** @var RoleInterface $obj */
        $obj = $this->find($id);

        return $obj;
    }

    /**
     * Finds a role by the given slug.
     *
     * @param  string  $slug
     * @return Role|RoleInterface
     */
    public function findBySlug(string $slug): ?RoleInterface
    {
        /** @var RoleInterface $role */
        $role = $this->findOneBy(['slug' => $slug]);

        return $role;
    }

    /**
     * @inheritdoc
     */
    public function findByName(string $name): ?RoleInterface
    {
        /** @var RoleInterface $role */
        $role = $this->findOneBy(['name' => $name]);

        return $role;
    }

    /**
     * @inheritdoc
     */
    public function create($name, $slug = null)
    {
        $role = $this->createRole($name, $slug);

        $this->save($role);

        return $role;
    }

    /**
     * @inheritdoc
     */
    public function save(Role $role)
    {
        $entityManager = $this->getEntityManager();

        $entityManager->persist($role);
        $entityManager->flush();
    }

    /**
     * @inheritdoc
     */
    public function delete(Role $role)
    {
        $entityManager = $this->getEntityManager();

        $entityManager->remove($role);
        $entityManager->flush();
    }

    /**
     * @inheritdoc
     */
    public function findAll()
    {
        return new Collection(parent::findAll());
    }

    /**
     * Get the entity name for this repository.
     * This entity MUST implement \Digbang\Security\Entities\Contracts\Role.
     *
     * @return string
     */
    abstract protected function entityName();

    /**
     * @param  string  $name
     * @param  string|null  $slug
     * @return Role
     */
    abstract protected function createRole($name, $slug = null);
}
