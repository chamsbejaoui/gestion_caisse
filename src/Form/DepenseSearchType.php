<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DepenseSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('montantMin', NumberType::class, [
                'label' => 'Montant minimum',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Montant minimum'
                ]
            ])
            ->add('montantMax', NumberType::class, [
                'label' => 'Montant maximum',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Montant maximum'
                ]
            ])
            ->add('description', TextType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Rechercher dans la description'
                ]
            ])
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }
}