<?php

namespace App\DtoRepository;

use App\Dto\EventDto;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\EntityManagerInterface;

class EventRepository
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {

    }

    public function findDueTasksByUser(
        int $userId,
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate,
    ): array {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id', 'id', 'integer')
            ->addScalarResult('title', 'title', 'string')
            ->addScalarResult('start', 'start', 'string')
            ->addScalarResult('end', 'end', 'string')
            ->addScalarResult('description', 'description', 'string')
            ->addScalarResult('user', 'user', 'string')
            ->addScalarResult('state', 'state', 'string')
            ->addScalarResult('activity', 'activity', 'string')
            ->addScalarResult('type', 'type', 'string');

        $rsm->newObjectMappings['id'] = [
            'className' => EventDto::class,
            'objIndex' => 0,
            'argIndex' => 0,
        ];
        $rsm->newObjectMappings['title'] = [
            'className' => EventDto::class,
            'objIndex' => 0,
            'argIndex' => 1,
        ];
        $rsm->newObjectMappings['start'] = [
            'className' => EventDto::class,
            'objIndex' => 0,
            'argIndex' => 2,
        ];
        $rsm->newObjectMappings['end'] = [
            'className' => EventDto::class,
            'objIndex' => 0,
            'argIndex' => 3,
        ];
        $rsm->newObjectMappings['description'] = [
            'className' => EventDto::class,
            'objIndex' => 0,
            'argIndex' => 4,
        ];
        $rsm->newObjectMappings['user'] = [
            'className' => EventDto::class,
            'objIndex' => 0,
            'argIndex' => 5,
        ];
        $rsm->newObjectMappings['state'] = [
            'className' => EventDto::class,
            'objIndex' => 0,
            'argIndex' => 6,
        ];
        $rsm->newObjectMappings['activity'] = [
            'className' => EventDto::class,
            'objIndex' => 0,
            'argIndex' => 7,
        ];
        $rsm->newObjectMappings['type'] = [
            'className' => EventDto::class,
            'objIndex' => 0,
            'argIndex' => 8,
        ];

        $sql = "
            SELECT
                dt.id,
                t.name as title,
                CONCAT(dt.date_due_task, 'T', dt.start_hour) as start,
                CONCAT(dt.date_due_task, 'T', dt.end_hour) as end,
                dt.comment as description,
                u.first_name as user,
                s.label as state,
                a.name as activity,
                tt.label as type
            FROM due_task dt
            JOIN task t ON dt.task_id = t.id
            JOIN \"user\" u ON dt.collaborator_id = u.id
            JOIN state s ON t.state_id = s.id
            JOIN activity a ON t.activity_id = a.id
            JOIN type_of_task tt ON t.type_of_task_id = tt.id
            WHERE dt.collaborator_id = :userId
        AND dt.date_due_task BETWEEN :startDate AND :endDate
        ";

        $query = $this->em->createNativeQuery($sql, $rsm);
        $query->setParameter('userId', $userId);
        $query->setParameter('startDate', $startDate, Types::DATE_IMMUTABLE);
        $query->setParameter('endDate', $endDate, Types::DATE_IMMUTABLE);

        return $query->getResult();
    }
}
