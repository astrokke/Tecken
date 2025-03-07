<?php

namespace App\Form;

use App\Entity\Client;
use App\Entity\Interlocutor;
use App\Repository\InterlocutorRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class InterlocutorFormType extends AbstractType
{
    public function __construct(
        private InterlocutorRepository $interlocutorRepository,
    ) {
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $client = $options['client'];
        $builder->setMethod('POST');
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($client) {
            $interlocutor = $event->getData();
            $form = $event->getForm();
            $repository = $this->interlocutorRepository;

            $form->add('firstName', TextType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'futuristic-input',
                    'placeholder' => 'Prénom',
                ],
            ])
                ->add('lastName', TextType::class, [
                    'label' => false,
                    'attr' => [
                        'class' => 'futuristic-input',
                        'placeholder' => 'Nom',
                    ],
                ])
                ->add('mail', EmailType::class, [
                    'label' => false,
                    'attr' => [
                        'class' => 'futuristic-input',
                        'placeholder' => 'Email',
                    ],
                ])
                ->add('phoneNumber', TextType::class, [
                    'label' => false,
                    'attr' => [
                        'class' => 'futuristic-input',
                        'placeholder' => 'Numéro de téléphone',
                    ],
                    'constraints' => [
                        new Length([
                            'max' => 15,
                            'maxMessage' => 'Le numéro de téléphone ne peut pas dépasser {{ limit }} chiffres.',
                        ]),
                ],
                ])
                ->add('job', TextType::class, [
                    'label' => false,
                    'attr' => [
                        'class' => 'futuristic-input',
                        'placeholder' => 'Poste',
                    ],
                ]);

            if ($client) {
                $form->add('client', HiddenType::class, [
                    'data' => $client->getId(),
                    'mapped' => false,
                ]);
            } else {
                $form->add('client', EntityType::class, [
                    'class' => Client::class,
                    'choice_label' => 'socialReason',
                    'label' => 'Entreprise',
                    'attr' => ['class' => 'futuristic-input'],
                ]);
            }

            $form->add('submit', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => ['class' => 'btn-futuristic'],
            ]);
        });
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Interlocutor::class,
            'client' => null,
        ]);
    }
}
