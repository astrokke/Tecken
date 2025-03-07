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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Length;

class TaskFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $task = $builder->getData();
        $builder->setAction($options["action"]);
        $builder->add('name', TextType::class, [
            'label' => false,
            'attr' => [
                'class' => 'futuristic-input',
                'placeholder' => 'Nom',
                'data-action' => 'blur->task-edit#update',
            ],
            'constraints' => [
                new Length([
                    'max' => 100,
                    'maxMessage' => 'La taille du texte ne doit pas dépasser 100 caractères !',
                ]),
            ],
        ])
        ->add('description', TextType::class, [
            'label' => false,
            'required' => false,
            'attr' => [
                'class' => 'futuristic-input',
                'placeholder' => 'Description',
            ],
        ])
        ->add('startDateForecast', DateType::class, [
            'widget' => 'single_text',
            'label' => 'Date prévisionnelle',
            'attr' => ['class' => 'futuristic-input'],
            'constraints' => [
                new GreaterThanOrEqual([
                    'value' => new \DateTime('2024-01-01'),
                    'message' => 'La date ne peut pas être inférieure à l\'année actuel !',
                ]),
                ],
        ])
        ->add('endDateForecast', DateType::class, [
            'widget' => 'single_text',
            'required' => false,
            'label' => 'Date de fin prévisionnelle',
            'attr' => [
                'class' => 'futuristic-input',
            ],
        ])
        ->add('durationForecast', NumberType::class, [
            'label' => false,
            'required' => false,
            'attr' => [
                'class' => 'futuristic-input',
                'placeholder' => 'Durée prévisionnelle en heure',
            ],
            'constraints' => [
                new Length([
                    'min' => 1,
                    'minMessage' => 'La valeur doit être supérieur à 0 !',
                ]),
            ],
        ])
        ->add('durationInvoiceReal', NumberType::class, [
            'label' => false,
            'required' => false,
            'attr' => [
                'class' => 'futuristic-input',
                'placeholder' => 'Durée Réel Facturable'
            ],
        ])
        ->add('state', EntityType::class, [
            'class' => State::class,
            'choice_label' => 'label',
            'label' => false,
            'attr' => ['class' => 'futuristic-input',],
        ])


        ->add('typeOfTask', EntityType::class, [
            'class' => TypeOfTask::class,
            'choice_label' => 'label',
            'label' => false ,
            'attr' => ['class' => 'futuristic-input'],
            ]);
        if ($task === null) {
            $builder->add('users', EntityType::class, [
                'class' => User::class,
                'query_builder' => function (UserRepository $ur): QueryBuilder {
                    return $ur->createQueryBuilder('u')
                        ->orderBy('u.firstName', 'ASC')
                        ->orderBy('u.lastName', 'ASC');
                },
                'choice_label' => 'firstName',
                'label' => false,
                'multiple' => true,
                'mapped' => false,
                'expanded' => true,
                'attr' => [
                    'class' => 'form-switch m-3',
                    'style' => 'display:grid; grid-template-columns: repeat(4,1fr); gap: 10px;'
                ],
            ]);

            $builder->add('startDateForecast', DateType::class, [
            'widget' => 'single_text',
            'label' => 'Date prévisionnelle',
            'attr' => ['class' => 'futuristic-input'],
            'constraints' => [
                new GreaterThanOrEqual([
                    'value' => new \DateTimeImmutable('2024-01-01'),
                    'message' => 'La date ne peut pas être inférieure à l\'année actuel !',
                ]),
                ],
            'data' => new \DateTimeImmutable(),
            ]);
            $builder->add('endDateForecast', DateType::class, [
            'widget' => 'single_text',
            'label' => 'Date de fin prévisionnelle',
            'attr' => ['class' => 'futuristic-input'],
            'constraints' => [
                new GreaterThanOrEqual([
                    'value' => new \DateTimeImmutable('2024-01-01'),
                    'message' => 'La date ne peut pas être inférieure à l\'année actuel !',
                ]),
                ],
            'data' => (new \DateTimeImmutable())->modify('+1 day'),
            ]);
        }

        $builder->add('submit', SubmitType::class, [
            'label' => 'Enregister',
            'attr' => ['class' => 'btn btn-futuristic'],
            ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}
