<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class DateConvertService
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public function extractDate(string $start): array
    {
        $date = explode('T', $start);
        $startDate = $date[0];
        $startHour = explode('+', $date[1]);
        $startHour = $startHour[0];

        return [$startDate, $startHour];
    }

    public function calculEndHour(
        \DateTimeImmutable $startHour,
        \DateTimeImmutable $duration,
    ): \DateTimeImmutable {
        $interval = $duration->format('g \h\o\u\r\s \+ i \m\i\n\u\t\e\s');
        $interval = \DateInterval::createFromDateString($interval);

        return $startHour->add($interval);
    }

    public function getMonthStartAndEnd(\DateTimeImmutable $date = null): array
    {
        if ($date === null) {
            $date = new \DateTimeImmutable();
        }
        $start = new \DateTimeImmutable($date->format('Y-m-01'));
        $end = (clone $start)->modify('last day of this month');

        return [
            'start' => $start,
            'end' => $end,
        ];
    }

    public function getWeekStartAndEnd(int $weekOffset = 0): array
    {
        $weekStart = (new \DateTimeImmutable())->modify("monday this week {$weekOffset} week");
        $weekEnd = (clone $weekStart)->modify('sunday this week');

        return [
            'start' => $weekStart,
            'end' => $weekEnd,
        ];
    }

    public function getYearStartAndEnd(int $yearOffset = 0): array
    {
        $year = (new \DateTimeImmutable())->modify("{$yearOffset} year")->format('Y');
        $yearStart = new \DateTimeImmutable("first day of January {$year}");
        $yearEnd = new \DateTimeImmutable("last day of December {$year}");

        return [
            'start' => $yearStart,
            'end' => $yearEnd,
        ];
    }

    public function getMonthInFrench(\DateTimeImmutable $date): string
    {
        setlocale(LC_TIME, 'fr_FR.UTF-8');
        return strftime('%B', $date->getTimestamp());
    }

    public function getWeekInFrench(\DateTimeImmutable $date): string
    {
        setlocale(LC_TIME, 'fr_FR.UTF-8');
        return strftime('%d %b %Y', $date->getTimestamp());
    }
}
