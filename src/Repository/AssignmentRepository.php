<?php

namespace App\Repository;

use App\Entity\Assignment;
use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;

/**
 * @extends ServiceEntityRepository<Assignment>
 */
class AssignmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry )
    {
        parent::__construct($registry, Assignment::class , );
    }

    public function save(Assignment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove (Assignment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
            // $this->getEntityManager()->detach($entity)1
        }
    }

    public function findByUserAndTask(int $userId, int $taskId): ?Assignment
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.task = :taskId')
            ->andWhere('a.collaborator = :userId')
            ->setParameters(new ArrayCollection([
                new Parameter('taskId', $taskId), 
                new Parameter('userId', $userId), 
            ]))
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getTotalDurationByTask(User $user, Task $task)
    {
        $qb = $this->createQueryBuilder('a')
            ->join('a.dueTask', 'd')
            ->where('a.collaborator = :user')
            ->andWhere('a.task = :task')
            ->setParameter('user', $user)
            ->setParameter('task', $task);
        
        $assignments = $qb->getQuery()->getResult();

        //TODO : Ajouter dans un service 
        $totalMinutes = 0;

        foreach ($assignments as $assignment) {
            foreach ($assignment->getDueTasks() as $dueTask) {
                if ($dueTask) {
                    $startHour = $dueTask->getStartHour()->format('H:i');
                    $endHour = $dueTask->getEndHour()->format('H:i');

                    list($startHours, $startMinutes) = explode(':', $startHour);
                    list($endHours, $endMinutes) = explode(':', $endHour);

                    $startTotalMinutes = ($startHours * 60) + $startMinutes;
                    $endTotalMinutes = ($endHours * 60) + $endMinutes;
                    $durationMinutes = $endTotalMinutes - $startTotalMinutes;

                    $totalMinutes += $durationMinutes;
                }
            }
        }

        $totalHours = intdiv($totalMinutes, 60);
        $totalMinutes = $totalMinutes % 60;

        return sprintf('%02d:%02d:00', $totalHours, $totalMinutes);

    }

}
    //    /**
    //     * @return Assignment[] Returns an array of Assignment objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Assignment
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
