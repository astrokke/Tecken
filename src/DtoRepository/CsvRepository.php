<?php

namespace App\DtoRepository;

use App\Dto\CsvDto;
use App\Entity\Activity;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;

class CsvRepository
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public function getCsvByMonth(
        string      $month,
        Activity    $activity,
    ): array {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('date', 'date', 'string')
            ->addScalarResult('full_name', 'full_name', 'string')
            ->addScalarResult('task', 'task', 'string')
            ->addScalarResult('duration', 'duration', 'string')
            ->addScalarResult('tjm', 'tjm', 'string')
            ->addScalarResult('total_ht', 'total_ht', 'string')
            ->addScalarResult('tva', 'tva', 'string')
            ->addScalarResult('total_ttc', 'total_ttc', 'string')
            ->addScalarResult('total_month', 'total_month', 'string')
        ->addScalarResult('activity_name', 'activity_name', 'string');
        $rsm->newObjectMappings['date'] = [
            'className' => CsvDto::class,
            'objIndex' => 0,
            'argIndex' => 0,
        ];
        $rsm->newObjectMappings['full_name'] = [
            'className' => CsvDto::class,
            'objIndex' => 0,
            'argIndex' => 1,
        ];
        $rsm->newObjectMappings['task'] = [
            'className' => CsvDto::class,
            'objIndex' => 0,
            'argIndex' => 2,
        ];
        $rsm->newObjectMappings['duration'] = [
            'className' => CsvDto::class,
            'objIndex' => 0,
            'argIndex' => 3,
        ];
        $rsm->newObjectMappings['tjm'] = [
            'className' => CsvDto::class,
            'objIndex' => 0,
            'argIndex' => 4,
        ];
        $rsm->newObjectMappings['total_ht'] = [
            'className' => CsvDto::class,
            'objIndex' => 0,
            'argIndex' => 5,
        ];
        $rsm->newObjectMappings['tva'] = [
            'className' => CsvDto::class,
            'objIndex' => 0,
            'argIndex' => 6,
        ];
        $rsm->newObjectMappings['total_ttc'] = [
            'className' => CsvDto::class,
            'objIndex' => 0,
            'argIndex' => 7,
        ];
        $rsm->newObjectMappings['total_month'] = [
            'className' => CsvDto::class,
            'objIndex' => 0,
            'argIndex' => 8,
        ];
        $rsm->newObjectMappings['activity_name'] = [
            'className' => CsvDto::class,
            'objIndex' => 0,
            'argIndex' => 9,
        ];
        $sql = "
             select 
                a.name as activity_name,
                dt.date_due_task as date,
                concat(u.first_name || ' ' || u.last_name) as full_name,
                t.name as task, 
                u.hour_rate_by_default as tjm, 
                (extract(hour from (dt.end_hour - dt.start_hour)) +  ROUND((extract(minute from (dt.end_hour - dt.start_hour))/60),1)) as duration, 
                ((extract(hour from (dt.end_hour - dt.start_hour)) +  (extract(minute from (dt.end_hour - dt.start_hour))/60))  * (u.hour_rate_by_default)) as total_ht, 
                (((extract(hour from (dt.end_hour - dt.start_hour)) +  (extract(minute from (dt.end_hour - dt.start_hour))/60))  * (u.hour_rate_by_default)) * 0.2) as TVA, 
                (((extract(hour from (dt.end_hour - dt.start_hour)) +  (extract(minute from (dt.end_hour - dt.start_hour))/60))  * (u.hour_rate_by_default)) * 1.2)  as total_ttc
            from due_task dt
            join task t on t.id=dt.task_id
            join \"user\" u on dt.collaborator_id = u.id
            join activity a on a.id=t.activity_id
            where a.id= :id
            and extract(month from (dt.date_due_task)) = :month
            order by dt.date_due_task asc 
        ";
        $query = $this->em->createNativeQuery($sql, $rsm);
        $query->setParameter('month', $month, Types::STRING);
        $query->setParameter('id', $activity->getId(), Types::INTEGER);
        $results = $query->getResult();

        $totalMonth = 0;
        foreach($results as $result) {
            $totalMonth += $result->getTotalTtc();
        }

        foreach ($results as $result) {
            $result->setTotalMonth($totalMonth);
        }

        return $results;
    }
}
