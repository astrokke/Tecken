<?php

namespace App\Repository;

use App\Entity\Task;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

use function pcov\waiting;

/**
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    public function save(Task $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Task $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
            // $this->getEntityManager()->detach($entity);
        }
    }

    public function searchTask(
        string $name,
        string $search,
    ): ?array {
        return $this->createQueryBuilder('t')
            ->andWhere('t.name LIKE :val')
            ->setParameter('val', '%'.$name.'%')
            ->addOrderBy('t.name', $search)
            ->getQuery()
            ->getResult();
    }

    public function countByAssignedTasksForUserAndPeriod(User $user, \DateTimeImmutable $start, \DateTimeImmutable $end): int
    {
        return $this->createQueryBuilder('t')
            ->select('COUNT(DISTINCT t.id)')
            ->join('t.assignments', 'a')
            ->where('a.collaborator = :user')
            ->andWhere('t.startDateForecast >= :start')
            ->andWhere('t.startDateForecast <= :end')
            ->setParameter('user', $user)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleScalarResult();
    }


    public function findByUserAndByOrder(User $user, array $states, string $order = 'ASC')
    {
        return $this->createQueryBuilder('t')
            ->join('t.assignments', 'a')
            ->join('t.state', 's')
            ->where('a.collaborator = :user')
            ->andWhere('s.label IN (:states)')
            ->orderBy('t.endDateForecast', $order)
            ->setParameter('user', $user)
            ->setParameter('states', $states)
            ->getQuery()
            ->getResult();
    }

    // TODO: refacto dans un DTO
    public function findTasksDeadline(int $userId, DateTimeImmutable $now, DateTimeImmutable $oneDayLater): array
    {
        $results = $this->createQueryBuilder('t')
            ->select('t.id', 't.name', 't.endDateForecast')
            ->innerJoin('t.assignments', 'a')
            ->innerJoin('a.collaborator', 'u')
            ->innerJoin('t.state', 's')
            ->where('u.id = :userId')
            ->andWhere('t.endDateForecast BETWEEN :now AND :oneDayLater')
            ->andWhere('s.label IN (:states)')
            ->setParameter('userId', $userId)
            ->setParameter('now', $now->format('Y-m-d'))
            ->setParameter('oneDayLater', $oneDayLater->format('Y-m-d'))
            ->setParameter('states', ['En cours', 'Non débuté'])
            ->orderBy('t.endDateForecast', 'ASC') // Tri par date de fin
            ->getQuery()
            ->getArrayResult();
        return array_map(function ($task) {
            $task['status'] = 'Upcomming';
            return $task;
        }, $results);
    }

    // TODO: facto dans un DTO
    public function findTasksPastDeadline(int $userId, DateTimeImmutable $now): array
    {
        $results = $this->createQueryBuilder('t')
            ->select('t.id', 't.name', 't.endDateForecast')
            ->innerJoin('t.assignments', 'a')
            ->innerJoin('a.collaborator', 'u')
            ->innerJoin('t.state', 's')
            ->where('u.id = :userId')
            ->andWhere('t.endDateForecast < :now')
            ->andWhere('s.label IN (:states)')
            ->setParameter('userId', $userId)
            ->setParameter('now', $now->format('Y-m-d'))
            ->setParameter('states', ['En cours', 'Non débuté'])
            ->orderBy('t.endDateForecast', 'ASC')
            ->getQuery()
            ->getArrayResult();
        return array_map(function ($task) {
            $task['status'] = 'Overdue';
            return $task;
        }, $results);
    }
    public function findCompletedTasksByActivityForCurrentMonth(): array
    {
        $startOfMonth = new \DateTimeImmutable('first day of this month');
        $endOfMonth = new \DateTimeImmutable('last day of this month');

        return $this->createQueryBuilder('t')
            ->select('a.id as activity_id, a.name as activity_name, COUNT(t.id) as completed_tasks')
            ->join('t.activity', 'a')
            ->join('t.state', 's')
            ->where('s.label = :completedState')
            ->andWhere('t.endDateForecast BETWEEN :startDate AND :endDate')
            ->groupBy('a.id')
            ->setParameter('completedState', 'Terminé')
            ->setParameter('startDate', $startOfMonth)
            ->setParameter('endDate', $endOfMonth)
            ->getQuery()
            ->getResult();
    }
    public function findCompletedTasksGroupedByActivity(): array
    {
        return $this->createQueryBuilder('t')
            ->select('a.id as activity_id, a.name as activity_name, COUNT(t.id) as completed_tasks')
            ->join('t.activity', 'a')
            ->join('t.state', 's')
            ->where('s.label = :completedState')
            ->groupBy('a.id')
            ->setParameter('completedState', 'Terminé')
            ->getQuery()
            ->getResult();
    }
    public function countTotalTasksForPeriod(
        \DateTimeImmutable $start,
        \DateTimeImmutable $end
    ): int {
        return $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.startDateForecast BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleScalarResult();
    }



    public function countAssignedTasksForPeriod(\DateTimeImmutable $start, \DateTimeImmutable $end): int
    {
        return $this->createQueryBuilder('t')
            ->select('COUNT(DISTINCT t.id)')
            ->innerJoin('t.assignments', 'a')
            ->where('t.startDateForecast BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countTasksByActivity(): array
    {
        return $this->createQueryBuilder('t')
            ->select('a.name as activityName, COUNT(t.id) as taskCount')
            ->join('t.activity', 'a')
            ->groupBy('a.id')
            ->getQuery()
            ->getResult();
    }

    public function countTotalTasksForCurrentMonth(
        \DateTimeImmutable $start,
        \DateTimeImmutable $end,
    ) {
        return (int) $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.startDateForecast BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleScalarResult();
    }

    // TODO: Refacto DTO + DTORepository
    public function findAllActivities(
        \DateTimeImmutable $start,
        \DateTimeImmutable $end,
    ) {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT
                a.name AS activity_name,
                COUNT(t.id) AS task_count,
                COALESCE(SUM(
                    CASE
                        WHEN a.billable THEN ROUND(t.duration_invoice_real * u.hour_rate_by_default)
                        ELSE 0
                        END
                    ), 0) AS total_billable 
            FROM
                activity a
            JOIN
                task t ON t.activity_id = a.id
            LEFT JOIN
                assignment ass ON ass.task_id = t.id
            LEFT JOIN
                "user" u ON u.id = ass.collaborator_id
            WHERE
                t.end_date_forecast BETWEEN :start AND :end
            GROUP BY
                a.id, a.name
            ORDER BY
                total_billable DESC
        ';

        $stmt = $conn->prepare($sql);
        return $stmt->execute([
            'start' => $start->format('Y-m-d'),
            'end' => $end->format('Y-m-d'),
        ])->fetchAllAssociative();

    }

    public function findActivitiesByYear(\DateTimeImmutable $start, \DateTimeImmutable $end)
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT EXTRACT(MONTH FROM t.start_date_forecast) AS month, COUNT(t.id) AS taskCount
            FROM task t
            WHERE t.start_date_forecast BETWEEN :start AND :end
            GROUP BY EXTRACT(MONTH FROM t.start_date_forecast)
            ORDER BY EXTRACT(MONTH FROM t.start_date_forecast) DESC
        ';
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'start' => $start->format('Y-m-d'),
            'end' => $end->format('Y-m-d'),
        ]);
        return $stmt->execute()->fetchAllAssociative();
    }

    public function findTasksByPeriodByActivity(
        \DateTimeImmutable $start,
        \DateTimeImmutable $end,
    ) {
        return $this->createQueryBuilder('t')
            ->select(
                'a.name as activityName, 
                    COUNT(t.id) as taskCount, 
                    SUM(t.durationForecast) as totalDuration, 
                    a.billable'
            )
            ->join('t.activity', 'a')
            ->where('t.endDateForecast BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->groupBy('a.id')
            ->getQuery()
            ->getResult();
    }

    public function getTotalBillingByPeriod(
        \DateTimeImmutable $start,
        \DateTimeImmutable $end,
    ) {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.assignments', 'a')
            ->leftJoin('a.collaborator', 'u')
            ->select('SUM(t.durationInvoiceReal * u.hourRateByDefault) AS totalBilling')
            ->leftJoin('t.activity', 'act')
            ->where('act.billable = true')
            ->andWhere('act.startDate BETWEEN :start AND :end')
            ->setParameter('start', $start->format('Y-m-d'))
            ->setParameter('end', $end->format('Y-m-d'))
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getTaskStatistics()
    {
        return $this->createQueryBuilder('t')
            ->select('COUNT(t.id) as totalTasks')
            ->addSelect("SUM(CASE WHEN s.label = 'Non débuté' THEN 1 ELSE 0 END) as notStartedTask")
            ->addSelect("SUM(CASE WHEN s.label = 'En cours' THEN 1 ELSE 0 END) as inProgressTask")
            ->addSelect("SUM(CASE WHEN s.label = 'Terminé' THEN 1 ELSE 0 END) as completedTask")
            ->addSelect("SUM(CASE WHEN s.label = 'Annulé' THEN 1 ELSE 0 END) as canceledTask")
            ->join('t.state', 's')
            ->getQuery()
            ->getSingleResult();

    }

    public function getTaskStatisticsByUser(User $user): array
    {
        return $this->createQueryBuilder('t')
        ->select('COUNT(t.id) AS totalTasks')
        ->addSelect("SUM(CASE WHEN s.label = 'Non débuté' THEN 1 ELSE 0 END) AS notStartedTask")
        ->addSelect("SUM(CASE WHEN s.label = 'En cours' THEN 1 ELSE 0 END) AS inProgressTask")
        ->addSelect("SUM(CASE WHEN s.label = 'Terminé' THEN 1 ELSE 0 END) AS completedTask")
        ->addSelect("SUM(CASE WHEN s.label = 'Annulé' THEN 1 ELSE 0 END) AS canceledTask")
        ->join('t.state', 's')
        ->join('t.assignments', 'a')
        ->where('a.collaborator = :user')
        ->setParameter('user', $user)
        ->getQuery()
        ->getSingleResult();
    }

    public function getTaskTypeCountByUser(User $user): array
    {
        return $this->createQueryBuilder('t')
            ->select('tt.label AS task_type, COUNT(t.id) AS task_count')
            ->join('t.typeOfTask', 'tt')
            ->join('t.assignments', 'a')
            ->where('a.collaborator = :user')
            ->setParameter('user', $user)
            ->groupBy('tt.label')
            ->getQuery()
            ->getResult();
    }

}
