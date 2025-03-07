<?php

namespace App\Controller;

use App\DtoRepository\MacroPlanningRepository;
use App\Entity\Activity;
use App\Entity\Milestone;
use App\Entity\Task;
use App\Form\MilestoneFormType;
use App\Form\MilestoneInteractiveFormType;
use App\Form\TaskInteractiveFormType;
use App\Repository\ActivityRepository;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use App\Service\ActivityService;
use App\Service\FormHandler;
use App\Service\TaskService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/macro-planning')]
class MacroPlanningController extends AbstractController
{
    #[Route('/{year}-{month}', name: 'viewMacroPlanning')]
    public function index(
        ActivityRepository $activityRepository,
        ?int $year = null,
        ?int $month = null,
    ): Response {
        if (!$month || !$year) {
            $date = new \DateTimeImmutable();
        } else {
            $date = \DateTimeImmutable::createFromFormat("Y-m", $year . '-' . $month);
        }

        $activities = $activityRepository->findAll();

        return $this->render('pages/macro_planning.html.twig', [
            "activities" => $activities,
            "date" => $date,
        ]);
    }

    #[Route('/ajax/{year}-{month}', name: 'ajax_get_MacroPlanning')]
    public function ajax(
        MacroPlanningRepository $macroPlanningRepository,
        SerializerInterface $serializer,
        int $year,
        int $month,
    ): Response {
        $date = \DateTimeImmutable::createFromFormat("Y-m-d", $year . '-' . $month . '-01');
        $json = [];
        foreach ($macroPlanningRepository->findByDate($date) as $milestone) {
            $json[] = $serializer->serialize($milestone, 'json');
            // dump($milestone);
        }

        return new JsonResponse($json, 200);
    }

    #[Route('/milestone/{id}', name: 'ajax_get_Milestone')]
    public function getMilestone(
        Request $request,
        FormHandler $formHandler,
        MacroPlanningRepository $macroPlanningRepository,
        Milestone $milestone,
        TaskService $taskService,
        ActivityService $activityService,
        UserRepository $userRepository,
        int $id,
    ): Response {
        $milestoneDto = $macroPlanningRepository->findOneById($id);
        $form = $this->createForm(MilestoneInteractiveFormType::class, $milestone, [
            'action' => $this->generateUrl('ajax_get_Milestone', [
                'id' => $milestone->getId()
            ])
        ]);
        if ($formHandler->handleForm($form, $request, true)) {
            return (new Response())->setStatusCode(Response::HTTP_OK);
        }
        $activity = $milestone->getActivity();
        $activityData = $activityService->getActivityData($activity);
        $page = $request->query->getInt('page', 1);
        $paginationDatas = $taskService->getPaginatedTasks($activity, $page);


        return $this->render('partial/macro_planning/_modal_content.html.twig', [
            'milestone' => $milestoneDto,
            'form' => $form,
            'activityData' => $activityData,
            'allCollaborators' => $userRepository->findAll(),
            'tasks' => $paginationDatas['tasks'],
            'currentPage' => $paginationDatas['currentPage'],
            'totalPages' => $paginationDatas['totalPages'],
            'activity' => $activity,


        ]);
    }

    #[Route(
        path: '/add/{id}',
        name: 'addMilestone',
        methods: ["GET", "POST"],
    )]
    public function addMilestone(
        Request $request,
        ?Activity $activity,
        FormHandler $formHandler,
    ): Response {
        // if (!$activity) {
        //     $this->addFlash('error', 'activité non trouvé');
        //     return $this->redirectToRoute('viewMacroPlanning');
        // }
        $milestone = new Milestone();
        $milestone->setActivity($activity);

        $form = $this->createForm(MilestoneFormType::class, $milestone);

        if ($formHandler->handleForm($form, $request, true)) {
            return $this->redirectToRoute('viewMacroPlanning');
        }

        return $this->render('partial/_form.html.twig', [
            'title' => 'Ajouter une milestone pour ' . $activity->getName(),
            'form' => $form->createView(),
            'milestone' => $milestone,
        ]);
    }

    #[Route(
        path: '/edit/{id}',
        name: 'editMilestone',
        methods: ["GET", "POST"],
    )]
    public function editMilestone(
        Request $request,
        ?Milestone $milestone,
        FormHandler $formHandler,
    ): Response {
        // if (!$activity) {
        //     $this->addFlash('error', 'activité non trouvé');
        //     return $this->redirectToRoute('viewMacroPlanning');
        // }

        $form = $this->createForm(MilestoneFormType::class, $milestone);

        if ($formHandler->handleForm($form, $request, true)) {
            return $this->redirectToRoute('viewMacroPlanning');
        }

        return $this->render('partial/_form.html.twig', [
            'title' => 'Modifier la milestone',
            'form' => $form->createView(),
            'milestone' => $milestone,
        ]);
    }
    #[Route(
        path: '/interactive/{id}/{milestoneId}',
        name: 'interactiveTaskForm',
        methods: ["GET", "POST"],
    )]
    public function interactiveTaskForm(
        Activity  $activity,
        TaskRepository $taskRepository,
        FormHandler $formHandler,
        FormFactoryInterface $formFactory,
        ?int $milestoneId = null,
    ): Response {
        if ($milestoneId) {
            $tasks = $taskRepository->findBy([
                'activity' => $activity,
                'milestone' => $milestoneId,
            ]);
        } else {
            $tasks =  $taskRepository->findBy(['activity' => $activity]);
        }
        $forms = [];
        foreach ($tasks as $index => $task) {
            $forms[] = [
               'form' => $formFactory->createNamed('task_form_'.$index, TaskInteractiveFormType::class, $task, [
                    'action' => $this->generateUrl('editTaskInteractive', [
                        'id' => $task->getId()
                    ])
                ]),
                'task' => $task,
            ];
        }

        return $this->render('partial/task/_interactive_form.html.twig', [
            'activity' => $activity,
            'forms' => $forms,
        ]);
    }

    #[Route(
        path: '/edit-interactive/{id}',
        name: 'editTaskInteractive',
        methods: ['GET', 'POST']
    )]
    public function editTaskInteractive(
        Request $request,
        Task $task,
        FormHandler $formHandler,
        FormFactoryInterface $formFactory,
    ): Response {
        if (!$task) {
            $this->addFlash('warning', 'Tâche non existante !');
            return (new Response())->setStatusCode(Response::HTTP_BAD_REQUEST);
        }
        $form_name = $request->getPayload()->keys()[0];
        $form = $formFactory->createNamed($form_name, TaskInteractiveFormType::class, $task);
        if ($formHandler->handleTaskForm($form, $request)) {
            return (new Response())->setStatusCode(Response::HTTP_OK);
        }
        return (new Response())->setStatusCode(Response::HTTP_BAD_REQUEST);
    }
}
