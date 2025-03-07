<?php

namespace App\Form;

use App\Entity\Activity;
use App\Entity\Client;
use App\Entity\Interlocutor;
use App\Entity\TypeOfActivity;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Length;

class ActivityFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $activity = $builder->getData();
        $builder->add('name', TextType::class, [
            'label' => false,
            'attr' => [
                'class' => 'futuristic-input',
                'placeholder' => 'Nom de l\'activité',
            ],
            'constraints' => [
                new Length([
                    'max' => 50,
                    'maxMessage' => 'Le texte ne doit pas dépasser 50 caractères !',
                ]),
            ],
        ])
            ->add('description', TextType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'futuristic-input',
                    'placeholder' => 'Description'
                ],
            ])
            ->add('startDate', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de début',
                'attr' => [
                    'class' => 'futuristic-input',
                ],
                'constraints' => [
                    new GreaterThanOrEqual([
                        'value' => new \DateTimeImmutable('2024-01-01'),
                        'message' => 'La date ne peut pas être inférieure à l\'année actuel !',
                    ]),
                ],
            ])
             ->add('endDate', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de fin',
                'attr' => [
                    'class' => 'futuristic-input',
                ],
                'constraints' => [
                    new GreaterThanOrEqual([
                        'value' => new \DateTimeImmutable('2024-01-01'),
                        'message' => 'La date ne peut pas être inférieure à l\'année actuel !',
                    ]),
                ],
            ])
            ->add('client', EntityType::class, [
                'class' => Client::class,
                'label' => 'Client',
                'choice_label' => 'socialReason',
                'attr' => [
                    'class' => 'futuristic-input',
                ],
                'required' => false,
            ])
            ->add('interlocutors', EntityType::class, [
                'class' => Interlocutor::class,
                'label' => 'Interlocuteur associé',
                'choice_label' => 'lastName',
                'multiple' => true,
                'attr' => ['class' => 'futuristic-input'],
                'required' => false,

            ])
             ->add('typeOfActivity', EntityType::class, [
                'class' => TypeOfActivity::class,
                'label' => 'Type d\'activité',
                'choice_label' => 'label',
                'attr' => ['class' => 'futuristic-input'],
            ])
            ->add('billable', CheckboxType::class, [
                'label' => 'L\'activité est facturable',
                'required' => false,
                'attr' => ['class' => 'form-check-input'],

            ]);
        if ($activity === null) {
            $builder->add('startDate', DateType::class, [
             'widget' => 'single_text',
             'label' => 'Date de début',
             'attr' => ['class' => 'futuristic-input'],
             'constraints' => [
                 new GreaterThanOrEqual([
                     'value' => new \DateTimeImmutable('2024-01-01'),
                     'message' => 'La date ne peut pas être inférieure à l\'année actuel !',
                 ]),
             ],
             'data' => new \DateTimeImmutable(),
            ]);
            $builder->add('endDate', DateType::class, [
             'widget' => 'single_text',
             'label' => 'Date de fin',
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
            'label' => 'Ajouter',
            'attr' => ['class' => 'btn-futuristic'],

        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Activity::class,
        ]);
    }
}
