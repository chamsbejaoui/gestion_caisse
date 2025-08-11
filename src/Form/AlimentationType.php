<?php

namespace App\Form;

use App\Entity\Alimentation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Positive;

class AlimentationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('montant', NumberType::class, [
                'required' => true,
                'attr' => [
                    'min' => '0.01',
                    'step' => '0.01',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir un montant.',
                    ]),
                    new Positive([
                        'message' => 'Le montant doit être un nombre positif.'
                    ])
                ],
            ])
            ->add('source', TextType::class, [
                'required' => true,
                'attr' => [
                    'minlength' => '2',
                    'maxlength' => '255',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir une source.',
                    ]),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'La source doit contenir au moins {{ limit }} caractères.',
                        'max' => 255,
                    ]),
                ],
            ])
            ->add('dateAction', DateTimeType::class, [
                'label' => 'Date de l\'action',
                'widget' => 'single_text',
                'required' => true,
                'input' => 'datetime_immutable',
                'empty_data' => null,
                'constraints' => [
                    new NotNull([
                        'message' => 'Veuillez sélectionner une date.',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Alimentation::class,
        ]);
    }
}
