<?php

namespace App\Service;

use App\Entity\Activity;
use App\Entity\Assignment;
use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class FormHandler
{
    public function __construct(
        private EntityManagerInterface $em,
        private FileUploader $fileUploader,
    ) {
    }

    public function handleForm(
        FormInterface   $form,
        Request         $request,
        bool            $flush = false,
    ): bool {
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->getName() === 'client_form') {
                $form->getData()->setSIREN(substr($form->getData()->getSiret(), 0, 9));
            }

            if (isset($form['imageFile']) && $form->get('imageFile')->getData()) {
                $this->fileUploader->uploadImage($form->get('imageFile')->getData(), $form);
            }
            $this->em->persist($form->getData());
            if ($flush) {
                $this->em->flush();
            }

            return true;
        }
        return false;
    }

    public function handleTaskForm(
        FormInterface $form,
        Request $request,
        ?Activity $activity = null
    ): bool {
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($activity) {
                $form->getData()->setActivity($activity);
            }
            if (isset($form['users'])) {
                foreach($form->get('users')->getData() as $user) {
                    $assignment = new Assignment();
                    $assignment->setCollaborator($user);
                    $form->getData()->addAssignment($assignment);
                    $this->em->persist($assignment);
                }
            }
            $this->em->persist($form->getData());
            $this->em->flush();
            return true;
        }
        return false;
    }
    public function handleFormInterlocutor(
        FormInterface $form,
        Request $request,
        ?Client $client = null
    ): bool {
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $interlocutor = $form->getData();
            if ($client) {
                $interlocutor->setClient($client);
            }
            $this->em->persist($interlocutor);
            $this->em->flush();
            return true;
        }
    
        return false;
    }
}