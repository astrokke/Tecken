<?php

namespace App\Form;

use App\Entity\Client;
use App\Repository\ClientRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;

class ClientFormType extends AbstractType
{
    public function __construct(
        private ClientRepository $clientRepository
    ) {
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->setMethod("POST");
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $client = $event->getData();
            $form = $event->getForm();


            $defaultOptions = [
                'attr' => [
                    'class' => 'form-control',
                    'style' => 'border: solid 1px #164479; border-style: none none inset hidden; border-radius: 0; padding: 0.375rem 0.75rem;'
                ]
            ];
            if(!$client->getId()) {
                $form->add('SIRET', TextType::class, [
                    'label' => false,
                    'sanitize_html' => true,
                    'attr' => [
                        'class' => 'futuristic-input',
                        'placeholder' => 'SIRET',
                    ],
                    'constraints' => [
                        new Length([
                            'max' => 14,
                            'maxMessage' => 'Le SIRET est trop long !',
                        ]),
                    ],
                ]);
            }
            $form->add('adress', TextType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'futuristic-input',
                    'placeholder' => 'Adresse',
                ],
                'constraints' => [
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'L\'adresse est trop longue !',
                    ]),
                ],
            ])
                ->add('socialReason', TextType::class, [
                    'label' => false,
                    'attr' => [
                        'class' => 'futuristic-input',
                        'placeholder' => 'Raison social',
                    ],
                    'constraints' => [
                        new Length([
                            'max' => 50,
                            'maxMessage' => 'La raison social est trop longue !',
                        ]),
                    ],
                ])
                ->add('phoneNumber', TextType::class, [
                    'label' => false,
                    'attr' => [
                        'class' => 'futuristic-input',
                        'placeholder' => 'Numéro de téléphone',
                    ],
                ])
                ->add('mail', EmailType::class, [
                    'label' => false,
                    'attr' => [
                        'class' => 'futuristic-input',
                        'placeholder' => 'Email',
                    ],
                    'constraints' => [
                        new Length([
                            'max' => 100,
                            'maxMessage' => 'L\'adresse email est trop longue !',
                        ]),
                    ],
                ])
                ->add('webSite', TextType::class, [
                    'label' => false,
                    'attr' => [
                        'class' => 'futuristic-input',
                        'placeholder' => 'Site web',
                    ],
                    'constraints' => [
                        new Length([
                            'max' => 100,
                            'maxMessage' => 'L\'adresse du site web est trop longue !',
                        ]),
                    ],
                ])
                ->add('postalCode', TextType::class, [
                    'label' => false,
                    'sanitize_html' => true,
                    'attr' => [
                        'class' => 'futuristic-input',
                        'placeholder' => 'Code postal',
                    ],
                    'constraints' => [
                        new Length([
                            'max' => 15,
                            'maxMessage' => 'Le code postale est trop long !',
                        ]),
                    ],
                ])
                ->add('city', TextType::class, [
                    'label' => false,
                    'attr' => [
                        'class' => 'futuristic-input',
                        'placeholder' => 'Ville',
                    ],
                    'constraints' => [
                        new Length([
                            'max' => 100,
                            'maxMessage' => 'Le nom de ville est trop long !',
                        ]),
                    ],
                ])
                ->add('TVANumber', TextType::class, [
                    'label' => false,
                    'sanitize_html' => true,
                    'attr' => [
                        'class' => 'futuristic-input',
                        'placeholder' => 'Code TVA',
                    ],
                    'constraints' => [
                        new Length([
                            'max' => 13,
                            'maxMessage' => 'Le code TVA est trop long !',
                        ]),
                    ],
                ])
                ->add('imageFile', FileType::class, [
                    'label' => 'Logo',
                    'mapped' => false,
                    'attr' => [
                        'class' => 'futuristic-input',
                    ],
                    'required' => false,
                    'constraints' => [
                        new File([
                            'maxSize' => '5M',
                            'mimeTypes' => [
                                'image/*',
                            ],
                            'mimeTypesMessage' => 'Veuillez télécharger un format d\'image valide',
                        ])
                    ],
                ])
                ->add('submit', SubmitType::class, [
                    'label' => 'Enregistrer',
                    'attr' => ['class' => 'btn-futuristic'],
                ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Client::class,
        ]);
    }
}
