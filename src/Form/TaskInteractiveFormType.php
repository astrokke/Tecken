<?php

namespace App\Form;

use App\Entity\State;
use App\Entity\Task;
use App\Entity\TypeOfTask;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

class TaskInteractiveFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $task = $builder->getData();
        if($options['action']) {
            $builder->setAction($options['action']);
        }
        $builder->add('name', TextType::class, [
            'label' => $task->getName(),
            'label_attr' => [
                'class' => '',
                'data-interactive-form-target' => 'label',
                'data-index' => 0,
                'data-name' => 'label',
                'data-type' => 'text',
                'data-action' => 'click->interactive-form#activateInput'
            ],
            'attr' => [
                'class' => 'form-control w-100',
                'data-interactive-form-target' => 'input',
                'data-index' => 0,
                'data-name' => 'label',
                'data-type' => 'text',
                'data-action' => 'focusout->interactive-form#closeInput'
            ],
        ])
        ->add('typeOfTask', EntityType::class, [
            'class' => TypeOfTask::class,
            'choice_label' => 'label',
            'label' => $task->getTypeOfTask()->getLabel(),
            'label_attr' => [
                'class' => '',
                'data-interactive-form-target' => 'label',
                'data-index' => 1,
                'data-name' => 'typeOfTask',
                'data-type' => 'select',
                'data-action' => 'click->interactive-form#activateInput'
            ],
            'attr' => [
                'class' => 'form-control w-100',
                'data-interactive-form-target' => 'input',
                'data-index' => 1,
                'data-name' => 'typeOfTask',
                'data-type' => 'select',
                'data-action' => 'focusout->interactive-form#closeInput'
            ],
        ])
        ->add('durationForecast', NumberType::class, [
            'label' => $task->getDurationForecastAsString(),
            'required' => false,
            'label_attr' => [
                'class' => '',
                'data-interactive-form-target' => 'label',
                'data-index' => 2,
                'data-name' => 'durationForecast',
                'data-type' => 'duration',
                'data-action' => 'click->interactive-form#activateInput'
            ],
            'attr' => [
                'class' => 'form-control w-100',
                'data-interactive-form-target' => 'input',
                'data-index' => 2,
                'data-name' => 'durationForecast',
                'data-type' => 'duration',
                'data-action' => 'focusout->interactive-form#closeInput'
            ],
        ])
        ->add('description', TextareaType::class, [
            'label' => 'Description',
            'required' => false,
            'label_attr' => [
                'class' => '',
                'data-interactive-form-target' => 'label',
                'data-index' => 3,
                'data-name' => 'description',
                'data-type' => 'text',
                'data-action' => 'click->interactive-form#activateInput'
            ],
            'attr' => [
                'class' => 'form-control w-100',
                'data-interactive-form-target' => 'input',
                'data-index' => 3,
                'data-name' => 'description',
                'data-type' => 'text',
                'data-action' => 'focusout->interactive-form#closeInput'
            ],
        ])
        // ->add('startDateForecast', DateType::class, [
        //     'widget' => 'single_text',
        //     'label' => 'Date prévisionnelle',
        //     'attr' => ['class' => 'futuristic-input'],
        //     'constraints' => [
        //         new GreaterThanOrEqual([
        //             'value' => new \DateTime('2024-01-01'),
        //             'message' => 'La date ne peut pas être inférieure à l\'année actuel !',
        //         ]),
        //     ],
        // ])
        // ->add('endDateForecast', DateType::class, [
        //     'widget' => 'single_text',
        //     'required' => false,
        //     'label' => 'Date de fin prévisionnelle',
        //     'attr' => ['class' => 'futuristic-input'],
        // ])
        // ->add('durationInvoiceReal', NumberType::class, [
        //     'label' => 'Durée Réel Facturable',
        //     'required' => false,
        //     'attr' => ['class' => 'futuristic-input'],
        // ])
        // ->add('state', EntityType::class, [
        //     'class' => State::class,
        //     'choice_label' => 'label',
        //     'label' => 'État de la tâche',
        //     'attr' => ['class' => 'futuristic-input'],
        // ])
        ->add('submit', SubmitType::class, [
            'label' => ' ',
            'row_attr' => ['class' => 'hidden-submit'],
            'attr' => [
                'data-interactive-form-target' => 'submit',
            ]
        ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
            'action' => '',
        ]);
    }
}
