<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Response;

class CsvExporter
{
    public function __construct()
    {
    }

    public function export(
        array   $datas,
        array   $headers,
        string  $filename,
    ): Response {
        // Flux en mémoire pour écrire le CSV
        $handle = fopen('php://memory', 'w');
        if ($handle === false) {
            die('Error write the file');
        }

        // fwrite($handle, "\xEF\xBB\BF");
        fputs($handle, (chr(0xEF) . chr(0xBB) . chr(0xBF)));
        fputcsv($handle, $headers, ';');

        // Écrit les données dans le CSV
        foreach ($datas as $data) {
            fputcsv($handle, [
                $data->getActivityName(),
                $data->getDate(),
                $data->getFullName(),
                $data->getTask(),
                $data->getDuration(),
                $data->getTjm(),
                $data->getTotalHT(),
                $data->getTva(),
                $data->getTotalTtc(),
                $data->getTotalMonth(),
            ], ";");
        }

        // Commencer à lire depuis le début
        rewind($handle);
        // dd(stream_get_contents($handle));

        $response = new Response(stream_get_contents($handle));
        // Ferme le flux
        fclose($handle);

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'"');
        return $response;
    }
}
