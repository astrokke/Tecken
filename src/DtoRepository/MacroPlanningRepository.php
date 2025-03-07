<?php
namespace App\DtoRepository;

use App\Dto\MilestoneDto;
use App\Dto\MilestoneUserDto;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;

class MacroPlanningRepository
{
    public function __construct(
        private EntityManagerInterface $em,
    )
    {   

    }

    public function findByDate(DateTimeImmutable $date): array
    { 
        $rsm = new ResultSetMapping;
        $rsm->addScalarResult('activity_id', 1, 'integer')
        ->addScalarResult('id', 2, 'integer')
        ->addScalarResult('label', 3, 'string')
        ->addScalarResult('start_date', 4, 'date_immutable')
        ->addScalarResult('date_end', 5, 'date_immutable')
        ->addScalarResult('completed_tasks', 6, 'integer')
        ->addScalarResult('task_count', 7, 'integer')
        ->addScalarResult('planning_start', 8, 'date_immutable')
        ->addScalarResult('planning_stop', 9, 'date_immutable');
        $rsm->newObjectMappings['activity_id'] = [
            'className' => MilestoneDto::class,
            'objIndex' => 0,
            'argIndex' => 0,
        ];
        $rsm->newObjectMappings['id'] = [
            'className' => MilestoneDto::class,
            'objIndex' => 0,
            'argIndex' => 1,
        ];
        $rsm->newObjectMappings['label'] = [
            'className' => MilestoneDto::class,
            'objIndex' => 0,
            'argIndex' => 2,
        ];
        $rsm->newObjectMappings['start_date'] = [
            'className' => MilestoneDto::class,
            'objIndex' => 0,
            'argIndex' => 3,
        ];
        $rsm->newObjectMappings['date_end'] = [
            'className' => MilestoneDto::class,
            'objIndex' => 0,
            'argIndex' => 4,
        ];
        $rsm->newObjectMappings['completed_tasks'] = [
            'className' => MilestoneDto::class,
            'objIndex' => 0,
            'argIndex' => 5,
        ];
        $rsm->newObjectMappings['task_count'] = [
            'className' => MilestoneDto::class,
            'objIndex' => 0,
            'argIndex' => 6,
        ];
        $rsm->newObjectMappings['planning_start'] = [
            'className' => MilestoneDto::class,
            'objIndex' => 0,
            'argIndex' => 7,
        ];
        $rsm->newObjectMappings['planning_stop'] = [
            'className' => MilestoneDto::class,
            'objIndex' => 0,
            'argIndex' => 8,
        ];
        $sql = "select m.activity_id, m.id, m.label, m.start_date, m.date_end, 
                    (select count(*) from task t1 
                    join state s on t1.state_id = s.id
                    where s.label = 'Terminé'
                    and m.id = t1.milestone_id) 
                    as completed_tasks,
                count(t.id) as task_count,
                :date_debut as planning_start,
                :date_fin as planning_stop
                from milestone m
                left join task t on t.milestone_id = m.id 
                where m.start_date > :date_debut and m.start_date < :date_fin 
                or m.date_end > :date_debut and m.date_end < :date_fin 
                or m.start_date < :date_debut and m.date_end > :date_fin
                group by m.activity_id, m.id, m.label, m.start_date, m.date_end;";

        $query = $this->em->createNativeQuery($sql, $rsm);
        $query->setParameter('date_debut', $date, Types::DATE_IMMUTABLE);
        $query->setParameter('date_fin', $date->add(\DateInterval::createFromDateString('10 month'))->format('Y-m-t'));
        $milestones = $query->getResult();
        foreach ($milestones as $milestone) {
            $milestone->setUsers($this->findMilestoneUsers($milestone->getMilestoneId()));
        }
        return $milestones;
    }

    public function findMilestoneUsers(int $milestoneId): array 
    {
        $rsm = new ResultSetMapping;
        $rsm->addScalarResult('first_name', 1, 'string')
        ->addScalarResult('last_name', 2, 'string');
        $rsm->newObjectMappings['first_name'] = [
            'className' => MilestoneUserDto::class,
            'objIndex' => 0,
            'argIndex' => 0,
        ];
        $rsm->newObjectMappings['last_name'] = [
            'className' => MilestoneUserDto::class,
            'objIndex' => 0,
            'argIndex' => 1,
        ];
        $sql = 'select distinct u.first_name, u.last_name from "user" u
                join assignment a on u.id = a.collaborator_id
                join task t on a.task_id = t.id
                where t.milestone_id = ?
                order by u.last_name, u.first_name;';
        $query = $this->em->createNativeQuery($sql, $rsm);
        $query->setParameter(1, $milestoneId);
        return $query->getResult();
    }

    public function findOneById(int $id): MilestoneDto|null
    {
        $rsm = new ResultSetMapping;
        $rsm->addScalarResult('activity_id', 1, 'integer')
        ->addScalarResult('id', 2, 'integer')
        ->addScalarResult('label', 3, 'string')
        ->addScalarResult('start_date', 4, 'date_immutable')
        ->addScalarResult('date_end', 5, 'date_immutable')
        ->addScalarResult('completed_tasks', 6, 'integer')
        ->addScalarResult('task_count', 7, 'integer')
        ->addScalarResult('planning_start', 8, 'date_immutable')
        ->addScalarResult('planning_stop', 9, 'date_immutable')
        ->addScalarResult('activity_label', 10, 'string');
        $rsm->newObjectMappings['activity_id'] = [
            'className' => MilestoneDto::class,
            'objIndex' => 0,
            'argIndex' => 0,
        ];
        $rsm->newObjectMappings['id'] = [
            'className' => MilestoneDto::class,
            'objIndex' => 0,
            'argIndex' => 1,
        ];
        $rsm->newObjectMappings['label'] = [
            'className' => MilestoneDto::class,
            'objIndex' => 0,
            'argIndex' => 2,
        ];
        $rsm->newObjectMappings['start_date'] = [
            'className' => MilestoneDto::class,
            'objIndex' => 0,
            'argIndex' => 3,
        ];
        $rsm->newObjectMappings['date_end'] = [
            'className' => MilestoneDto::class,
            'objIndex' => 0,
            'argIndex' => 4,
        ];
        $rsm->newObjectMappings['completed_tasks'] = [
            'className' => MilestoneDto::class,
            'objIndex' => 0,
            'argIndex' => 5,
        ];
        $rsm->newObjectMappings['task_count'] = [
            'className' => MilestoneDto::class,
            'objIndex' => 0,
            'argIndex' => 6,
        ];
        $rsm->newObjectMappings['planning_start'] = [
            'className' => MilestoneDto::class,
            'objIndex' => 0,
            'argIndex' => 7,
        ];
        $rsm->newObjectMappings['planning_stop'] = [
            'className' => MilestoneDto::class,
            'objIndex' => 0,
            'argIndex' => 8,
        ];
        $rsm->newObjectMappings['activity_label'] = [
            'className' => MilestoneDto::class,
            'objIndex' => 0,
            'argIndex' => 9,
        ];
        // dd($rsm);
        $sql = "select m.activity_id, m.id, m.label, m.start_date, m.date_end, 
                    (select count(*) from task t1 
                    join state s on t1.state_id = s.id
                    where s.label = 'Terminé'
                    and m.id = t1.milestone_id) 
                    as completed_tasks,
                count(t.id) as task_count,
                :date as planning_start,
                :date as planning_stop,
                a.name as activity_label
                from milestone m
                join activity a on m.activity_id = a.id
                left join task t on t.milestone_id = m.id 
                where m.id = :id
                group by m.activity_id, m.id, m.label, m.start_date, m.date_end, a.name;";

        $query = $this->em->createNativeQuery($sql, $rsm);
        $query->setParameter('date', \DateTimeImmutable::createFromFormat("Y-m-d", "2024-01-01"), Types::DATE_IMMUTABLE);
        $query->setParameter('id', $id);
        $milestone = $query->getOneOrNullResult();
        $milestone->setUsers($this->findMilestoneUsers($milestone->getMilestoneId()));
        return $milestone;
    }
}
