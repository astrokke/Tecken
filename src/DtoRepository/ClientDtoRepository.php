<?php

namespace App\DtoRepository;

use App\Dto\ClientDto;
use App\Dto\CsvDto;
use App\Entity\Activity;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;

class ClientDtoRepository
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public function getDataByActivityForMonth(
        string      $month,
        Activity    $activity,
    ): array {
        $rsm = new ResultSetMapping();
        $rsm
            ->addScalarResult('task', 'task', 'string')
            ->addScalarResult('state', 'state', 'string')
            ->addScalarResult('full_name', 'full_name', 'string')
            ->addScalarResult('date', 'date', 'string')
            ->addScalarResult('duration', 'duration', 'float')
            ->addScalarResult('tjm', 'tjm', 'string')
            ->addScalarResult('total_ht', 'total_ht', 'string')
            ->addScalarResult('tva', 'tva', 'string')
            ->addScalarResult('total_ttc', 'total_ttc', 'string')
            ->addScalarResult('total_month', 'total_month', 'string');
        $rsm->newObjectMappings['task'] = [
            'className' => ClientDto::class,
            'objIndex' => 0,
            'argIndex' => 0,
        ];
        $rsm->newObjectMappings['state'] = [
            'className' => ClientDto::class,
            'objIndex' => 0,
            'argIndex' => 1,
        ];
        $rsm->newObjectMappings['full_name'] = [
            'className' => ClientDto::class,
            'objIndex' => 0,
            'argIndex' => 2,
        ];
        $rsm->newObjectMappings['date'] = [
            'className' => ClientDto::class,
            'objIndex' => 0,
            'argIndex' => 3,
        ];
        $rsm->newObjectMappings['duration'] = [
            'className' => ClientDto::class,
            'objIndex' => 0,
            'argIndex' => 4,
        ];
        $rsm->newObjectMappings['tjm'] = [
            'className' => ClientDto::class,
            'objIndex' => 0,
            'argIndex' => 5,
        ];
        $rsm->newObjectMappings['total_ht'] = [
            'className' => ClientDto::class,
            'objIndex' => 0,
            'argIndex' => 6,
        ];
        $rsm->newObjectMappings['tva'] = [
            'className' => ClientDto::class,
            'objIndex' => 0,
            'argIndex' => 7,
        ];
        $rsm->newObjectMappings['total_ttc'] = [
            'className' => ClientDto::class,
            'objIndex' => 0,
            'argIndex' => 8,
        ];
        $rsm->newObjectMappings['total_month'] = [
            'className' => ClientDto::class,
            'objIndex' => 0,
            'argIndex' => 9,
        ];
        $sql = "
        SELECT 
            t.name AS task, 
            st.label AS state,
            CONCAT(u.first_name || ' ' || u.last_name) AS full_name,
            dt.date_due_task AS date,
            (EXTRACT(hour FROM (dt.end_hour - dt.start_hour)) + 
             ((EXTRACT(minute FROM (dt.end_hour - dt.start_hour)) / 60)) 
            ) AS duration, 
            ROUND(u.hour_rate_by_default) AS tjm, 
            ROUND((EXTRACT(hour FROM (dt.end_hour - dt.start_hour)) +  
              (EXTRACT(minute FROM (dt.end_hour - dt.start_hour)) / 60)) * 
             (u.hour_rate_by_default)
            ) AS total_ht, 
            ROUND(((EXTRACT(hour FROM (dt.end_hour - dt.start_hour)) +  
              (EXTRACT(minute FROM (dt.end_hour - dt.start_hour)) / 60)) * 
             (u.hour_rate_by_default)) * 0.2) AS TVA, 
            ROUND(((EXTRACT(hour FROM (dt.end_hour - dt.start_hour)) +  
              (EXTRACT(minute FROM (dt.end_hour - dt.start_hour)) / 60)) * 
             ROUND(u.hour_rate_by_default)) * 1.2) AS total_ttc
        FROM 
            due_task dt
        JOIN 
            task t ON t.id = dt.task_id
        JOIN 
            \"user\" u ON dt.collaborator_id = u.id
        JOIN 
            activity a ON a.id = t.activity_id
        JOIN 
            state st ON t.state_id = st.id
        WHERE 
            a.id = :id
            AND EXTRACT(month FROM (dt.date_due_task)) = :month
        ORDER BY 
            dt.date_due_task ASC; 
        ";
        $query = $this->em->createNativeQuery($sql, $rsm);
        $query->setParameter('month', $month, Types::STRING);
        $query->setParameter('id', $activity->getId(), Types::INTEGER);
        $results = $query->getResult();

        $totalMonth = 0;
        foreach($results as $result) {
            if($result->getTotalTtc() !== null) {
                $totalMonth += $result->getTotalTtc();
            }
        }

        foreach ($results as $result) {
            $result->setTotalMonth($totalMonth);
        }
        return $results;
    }


}
