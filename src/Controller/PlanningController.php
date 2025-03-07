<?php

namespace App\Controller;

use App\Dto\PlannedTaskDto;
use App\Repository\DueTaskRepository;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PlanningController extends AbstractController
{
    #[Route('/planning/{week}', name: 'viewPlanning')]
    public function index(UserRepository $userRepository, int $week = null): Response
    {

        $now = new DateTimeImmutable();
        $year = $now->format("Y");
        if(!$week) {
            $week = $now->format('W');
        }

        $users = $userRepository->findAll();

        return $this->render('pages/planning.html.twig', [
            "users" => $users,
            "week_number" => sprintf('%02d', $week),
            "date_lundi" => $now->setISODate($year, $week, 1)->format("d/m"),
            "date_mardi" => $now->setISODate($year, $week, 2)->format("d/m"),
            "date_mercredi" => $now->setISODate($year, $week, 3)->format("d/m"),
            "date_jeudi" => $now->setISODate($year, $week, 4)->format("d/m"),
            "date_vendredi" => $now->setISODate($year, $week, 5)->format("d/m"),
        ]);
    }

    #[Route('/planning/ajax/{week}', methods: ['GET'], name: 'ajax_get_planning')]
    public function ajaxPlanning(UserRepository $userRepository, DueTaskRepository $dueTaskRepository, string $week): JsonResponse
    {
        $dueTasks = $dueTaskRepository->findAll();

        $json = [];
        foreach($dueTasks as $dueTask) {
            if($dueTask->getDateDueTask()->format('W') !== $week) {
                continue;
            }
            // Calcul taille du container par rapport à la durée
            $startHour = $dueTask->getStartHour()->format('H');
            $startMinute = $dueTask->getStartHour()->format('i');
            $startMinuteFormated = intval($startMinute) / 60;
            $startPercent = number_format(($startHour + $startMinuteFormated - 9.5) * 100 / 8, 2);

            $endHour = $dueTask->getEndHour()->format('H');
            $endMinute = $dueTask->getEndHour()->format('i');
            $endMinuteFormated = intval($endMinute) / 60;
            $endPercent = number_format(($endHour + $endMinuteFormated - 9.5) * 100 / 8, 2);

            $clientName = null;
            if ($dueTask->getAssignment()->getTask()->getActivity()->getClient()) {
                $clientName =  $dueTask->getAssignment()->getTask()->getActivity()->getClient()->getSocialReason();
            }

            $dto = new PlannedTaskDto(
                userId: $dueTask->getAssignment()->getCollaborator()->getId(),
                taskName: $dueTask->getAssignment()->getTask()->getName(),
                clientName: $clientName,
                activityName: $dueTask->getAssignment()->getTask()->getActivity()->getName(),
                taskType: $dueTask->getAssignment()->getTask()->getTypeOfTask()->getLabel(),
                day: $dueTask->getDateDueTask()->format('d/m'),
                startHour: $dueTask->getStartHour()->format('H:i'),
                endHour: $dueTask->getEndHour()->format('H:i'),
                startPercent: $startPercent,
                endPercent: $endPercent,
                color: $dueTask->getAssignment()->getTask()->getTypeOfTask()->getColor(),
            );
            $json[] = $dto->toArray();
        }

        return new JsonResponse($json, 200);
    }
}
