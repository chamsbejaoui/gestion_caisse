<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExportOptionsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateMin', DateType::class, [
                'label' => 'Date minimum',
                'required' => false,
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('dateMax', DateType::class, [
                'label' => 'Date maximum',
                'required' => false,
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('format', ChoiceType::class, [
                'label' => 'Format d\'exportation',
                'required' => true,
                'choices' => [
                    'CSV' => 'csv',
                    'Excel (XLSX)' => 'xlsx',
                    'PDF' => 'pdf'
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('exportAll', ChoiceType::class, [
                'label' => 'Données à exporter',
                'required' => true,
                'choices' => [
                    'Toutes les données' => true,
                    'Période personnalisée' => false
                ],
                'expanded' => true,
                'multiple' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
