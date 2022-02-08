<?php
declare(strict_types=1);

namespace App\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\ORMInvalidArgumentException;

abstract class AbstractRepository extends EntityRepository
{
    final public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, new ClassMetadata($this->getEntityClassName(), new UnderscoreNamingStrategy(\CASE_LOWER, true)));
    }
    /**
     * Tells the EntityManager to make an instance managed and persistent.
     *
     * The entity will be entered into the database at or before transaction
     * commit or as a result of the flush operation.
     *
     * NOTE: The persist operation always considers entities that are not yet known to
     * this EntityManager as NEW. Do not pass detached entities to the persist operation.
     *
     * @throws ORMException
     */
    public function persist($object): void
    {
        $this->_em->persist($object);
    }

    /**
     * Flushes all changes to objects that have been queued up to now to the database.
     * This effectively synchronizes the in-memory state of managed objects with the
     * database.
     *
     * If an entity is explicitly passed to this method only this entity and
     * the cascade-persist semantics + scheduled inserts/removals are synchronized.
     *
     * @param mixed|null $object the entity to flush (optional)
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function flush(mixed $object = null): void
    {
        $this->_em->flush($object);
    }

    /**
     * Refreshes the state of the given entity from the database, overwriting
     * any local, un-persisted changes.
     */
    public function refresh($object): void
    {
        $this->_em->getUnitOfWork()->refresh($object);
    }

    /**
     * Removes an entity instance.
     *
     * A removed entity will be removed from the database at or before transaction commit
     * or as a result of the flush operation.
     *
     * @throws ORMInvalidArgumentException
     * @throws ORMException
     */
    public function remove($object): void
    {
        $this->_em->remove($object);
    }

    /**
     * Returns the fully qualified class name of the entity for this repository.
     */
    abstract protected function getEntityClassName(): string;


}
