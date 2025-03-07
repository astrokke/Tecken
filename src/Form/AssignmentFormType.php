<?php

namespace App\Form;

use App\Entity\Assignment;
use App\Entity\Task;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssignmentFormType extends AbstractType
{
    public function __construct(private UserRepository $userRepository)
    {
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $task = $options['task'];
        $collaborators = $options['collaborators'];
        $builder->setMethod('POST');
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) use ($task, $collaborators){
            $assignment = $event->getData();
            $form = $event->getForm();
                $form->add('hourRate', NumberType::class, [
                    'label' => 'Taux Horaire',
                    'attr' => ['class' => 'futuristic-input'],
                    'required' => false,
                ])
                ->add('collaborator', EntityType::class, [
                    'class' => User::class,
                    'choice_label' => 'firstName',
                    'label' => 'Est assigné à',
                    'choices' => $collaborators,
                    'attr' => ['class' => 'futuristic-input'],
                ])
                ->add('submit', SubmitType::class, [
                    'label' => 'Valider',
                    'attr' => ['class' => 'btn-futuristic'],
                ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Assignment::class,
            'task' => null,
            'collaborators' => null,
        ]);
    }
}