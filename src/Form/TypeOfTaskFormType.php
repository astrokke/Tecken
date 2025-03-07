<?php

namespace App\Form;

use App\Entity\TypeOfTask;
use Doctrine\DBAL\Types\FloatType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class TypeOfTaskFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $tot = $builder->getData();
        $builder->add('label', TextType::class, [
            'label' => false,
            'attr' => [
                'class' => 'futuristic-input',
                'placeholder' => 'Nom du type de tâche',
            ],
            'constraints' => [
                new Length([
                    'max' => 100,
                    'maxMessage' => 'Le texte ne doit pas dépasser 50 caractères !',
                ]),
            ],
        ])
            ->add('coefHourRate', NumberType::class, [
                'label' => 'Coef du type de tâche',
                'attr' => ['class' => 'futuristic-input'],
            ])
            ->add('color', TextType::class, [
            'label' => false,
            'attr' => [
                'class' => 'futuristic-input',
                'placeholder' => 'Couleur du type de tâche en rgb ou hexa',
            ],
            'constraints' => [
                new Length([
                    'max' => 31,
                    'maxMessage' => 'Le texte ne doit pas dépasser 31 caractères !',
                ]),
            ],
        ]);
        $builder->add('submit', SubmitType::class, [
            'label' => 'Ajouter',
            'attr' => ['class' => 'btn-futuristic'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TypeOfTask::class,
        ]);
    }
}
