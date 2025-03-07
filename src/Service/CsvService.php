<?php

namespace App\Service;

use App\Entity\Activity;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Response;

class CsvService
{
    public function __construct(
        public CsvExporter     $csvExporter,
    ) {
    }

    public function exportCsvByMonth(
        ?Activity            $activity,
        \DateTimeImmutable  $date,
        array               $csvDatas,
    ): Response {
        $totalMonth = 0;
        foreach($csvDatas as $data) {
            $totalMonth += $data->getTotalTtc();
        }

        foreach($csvDatas as &$data) {
            $data->setTotalMonth($totalMonth);
        }
        $filename = sprintf('%s-%s.csv', $activity->getName(), $date->format('Y-m'));

        $headers = ['Activité','Date', 'Nom/Prénom', 'Tâche', 'Durée', 'TJM', 'TotalHT', 'TVA', 'TotalTTC', 'Total'];
        return $this->csvExporter->export($csvDatas, $headers, $filename);
    }
}
