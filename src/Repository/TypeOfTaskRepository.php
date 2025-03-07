<?php

namespace App\Repository;

use App\Entity\TypeOfTask;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TypeOfTask>
 */
class TypeOfTaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypeOfTask::class);
    }

    public function save(TypeOfTask $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TypeOfTask $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
            // $this->getEntityManager()->detach($entity);
        }
    }

    public function canBeDeleted(TypeOfTask $tot): bool
    {
        return $this->createQueryBuilder('t')
            ->select('COUNT(task.id)')
            ->innerJoin('t.type', 'task')
            ->where('t.id = :typeId')
            ->setParameter('typeId', $tot->getId())
            ->getQuery()
            ->getSingleScalarResult() == 0;
    }

    //    /**
    //     * @return TypeOfTask[] Returns an array of TypeOfTask objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?TypeOfTask
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
