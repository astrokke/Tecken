<?php

namespace App\DtoRepository;

use App\Dto\WorkloadDto;
use App\Entity\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;

class WorkloadRepository
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public function findTimeTotalByDate(
        \DateTimeImmutable  $startDate,
        \DateTimeImmutable  $endDate,
    ): array {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('activity_name', 1, 'string')
            ->addScalarResult('first_name', 2, 'string')
            ->addScalarResult('last_name', 3, 'string')
            ->addScalarResult('time', 4, 'string');
        $rsm->newObjectMappings['activity_name'] = [
            'className' => WorkloadDto::class,
            'objIndex' => 0,
            'argIndex' => 0,
        ];
        $rsm->newObjectMappings['time'] = [
            'className' => WorkloadDto::class,
            'objIndex' => 0,
            'argIndex' => 1,
        ];
        $rsm->newObjectMappings['first_name'] = [
            'className' => WorkloadDto::class,
            'objIndex' => 0,
            'argIndex' => 2,
        ];
        $rsm->newObjectMappings['last_name'] = [
            'className' => WorkloadDto::class,
            'objIndex' => 0,
            'argIndex' => 3,
        ];
        $sql = '
        select 
            a."name" as activity_name, b.time, b.first_name, b.last_name
        from activity a,
        LATERAL (
            select 
                dt.collaborator_id,
                u.first_name,
                u.last_name,
                sum(dt.end_hour - dt.start_hour) as time
                from due_task dt 
                join task t on t.id = dt.task_id 
                join "user" u on dt.collaborator_id=u.id
                where 
                    t.activity_id = a.id 
                    and dt.date_due_task between :startDate and :endDate
                    group by dt.collaborator_id, u.first_name, u.last_name
        ) as b
        ';
        $query = $this->em->createNativeQuery($sql, $rsm);
        $query->setParameter('startDate', $startDate, Types::DATE_IMMUTABLE);
        $query->setParameter('endDate', $endDate, Types::DATE_IMMUTABLE);
        return $query->getResult();

    }

    public function findTimeTotalForAllByDate(
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate,
    ): array {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('activity_name', 'activity_name', 'string')
            ->addScalarResult('time_total', 'time_total', 'string');
        $rsm->newObjectMappings['activity_name'] = [
            'className' => WorkloadDto::class,
            'objIndex' => 0,
            'argIndex' => 0,
        ];
        $rsm->newObjectMappings['time_total'] = [
            'className' => WorkloadDto::class,
            'objIndex' => 0,
            'argIndex' => 1,
        ];

        $sql = '
                select 
                    a."name" as activity_name,
                    sum(dt.end_hour - dt.start_hour) as time_total
                from due_task dt 
                join task t on t.id = dt.task_id 
                join activity a on a.id = t.activity_id
                where dt.date_due_task between :startDate and :endDate
                group by a."name"
                ';
        $query = $this->em->createNativeQuery($sql, $rsm);
        $query->setParameter('startDate', $startDate, Types::DATE_IMMUTABLE);
        $query->setParameter('endDate', $endDate, Types::DATE_IMMUTABLE);
        return $query->getResult();
    }

    public function findTimeTotalByUser(
        \DateTimeImmutable  $startDate,
        \DateTimeImmutable  $endDate,
        User                $user,
    ) {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('activity_name', 'activity_name', 'string')
            ->addScalarResult('time_total', 'time_total', 'string');
        $rsm->newObjectMappings['activity_name'] = [
            'className' => WorkloadDto::class,
            'objIndex' => 0,
            'argIndex' => 0,
        ];
        $rsm->newObjectMappings['time_total'] = [
            'className' => WorkloadDto::class,
            'objIndex' => 0,
            'argIndex' => 1,
        ];
        $sql = '
            select 
                a."name" as activity_name,
                sum(dt.end_hour - dt.start_hour) as time_total
            from due_task dt 
            join task t on t.id = dt.task_id 
            join activity a on a.id = t.activity_id
            where dt.collaborator_id = :userId
            and dt.date_due_task between :startDate and :endDate
            group by a."name"
        ';
        $query = $this->em->createNativeQuery($sql, $rsm);
        $query->setParameter('startDate', $startDate, Types::DATE_IMMUTABLE);
        $query->setParameter('endDate', $endDate, Types::DATE_IMMUTABLE);
        $query->setParameter('userId', $user->getId(), Types::INTEGER);
        return $query->getResult();
    }
}
