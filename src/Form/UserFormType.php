<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Bundle\SecurityBundle\Security;

class UserFormType extends AbstractType
{

    public function __construct(private Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('mail', EmailType::class, [
                'label' => 'Email',
                'attr' => ['class' => 'futuristic-input'],
            ])
            ->add('firstName', TextType::class, [
                'required' => false,
                'label' => 'Prénom',
                'attr' => ['class' => 'futuristic-input'],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'attr' => ['class' => 'futuristic-input'],
            ])
            ->add('phoneNumber', TextType::class, [
                'required' => false,
                'label' => 'Numéro de téléphone',
                'attr' => ['class' => 'futuristic-input'],
            ])
            ->add('job', TextType::class, [
                'required' => false,
                'label' => 'Poste',
                'attr' => ['class' => 'futuristic-input'],
            ]);


        if ($this->security->isGranted('ROLE_MANAGER') || $this->security->isGranted('ROLE_DIRECTEUR')) {
            $builder->add('roles', ChoiceType::class, [
                'label' => 'Rôles',
                'choices' => [
                    'Utilisateur' => 'ROLE_USER',
                    'Manager' => 'ROLE_MANAGER',
                    'Directeur' => 'ROLE_DIRECTEUR',
                    'Administrateur' => 'ROLE_ADMIN',
                ],
                'expanded' => true, // Affichage sous forme de cases à cocher
                'multiple' => true,
                'attr' => ['class' => 'futuristic-checkbox-group futuristic-input'],
            ]);
        }

        $builder
            ->add('hourRateByDefault', TextType::class, [
                'required' => false,
                'label' => 'Taux horaire',
                'attr' => ['class' => 'futuristic-input'],
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Avatar',
                'mapped' => false,
                'attr' => ['class' => 'futuristic-input'],
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => ['image/*'],
                        'mimeTypesMessage' => 'Veuillez télécharger un format d\'image valide',
                    ])
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Modifier le profil',
                'attr' => ['class' => 'btn-futuristic'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
