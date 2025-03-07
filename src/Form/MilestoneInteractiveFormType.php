<?php

namespace App\Form;

use App\Entity\Milestone;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MilestoneInteractiveFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $milestone = $options['data'];
        $dateFormatter = new \IntlDateFormatter('fr_FR', null, null);
        $dateFormatter->setPattern('d MMMM yyyy');
        $startDate = $dateFormatter->format($milestone->getStartDate());
        $endDate = $dateFormatter->format($milestone->getDateEnd());

        if($options['action']) {
            $builder->setAction($options['action']);
        }

        $builder->add('label', TextType::class, [
            'label' => $milestone->getLabel(),
            'label_attr' => [
                'class' => 'h4 mb-1',
                'data-interactive-form-target' => 'label',
                'data-index' => 0,
                'data-name' => 'label',
                'data-type' => 'text',
                'data-action' => 'click->interactive-form#activateInput'
            ],
            'attr' => [
                'class' => 'h4 mb-0',
                'data-interactive-form-target' => 'input',
                'data-index' => 0,
                'data-name' => 'label',
                'data-type' => 'text',
                'data-action' => 'focusout->interactive-form#closeInput'
            ],
            'row_attr' => ['']
        ])
        ->add('startDate', DateType::class, [
            'label' => $startDate,
            'widget' => 'single_text',
            'label_attr' => [
                'class' => 'h5',
                'data-interactive-form-target' => 'label',
                'data-index' => 1,
                'data-name' => 'startDate',
                'data-type' => 'date',
                'data-action' => 'click->interactive-form#activateInput'
            ],
            'attr' => [
                'data-interactive-form-target' => 'input',
                'data-index' => 1,
                'data-name' => 'startDate',
                'data-type' => 'date',
                'data-action' => 'focusout->interactive-form#closeInput'
            ],
        ])
        ->add('dateEnd', DateType::class, [
            'label' => $endDate,
            'widget' => 'single_text',
            'label_attr' => [
                'class' => 'h5',
                'data-interactive-form-target' => 'label',
                'data-index' => 2,
                'data-name' => 'endDate',
                'data-type' => 'date',
                'data-action' => 'click->interactive-form#activateInput'
            ],
            'attr' => [
                'data-interactive-form-target' => 'input',
                'data-index' => 2,
                'data-name' => 'endDate',
                'data-type' => 'date',
                'data-action' => 'focusout->interactive-form#closeInput'
            ],
        ])
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
            'data_class' => Milestone::class,
            'action' => '',
        ]);
    }
}
