<?php

namespace App\Form;

use App\Entity\Caisse;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;
use Symfony\Component\Validator\Constraints\LessThan;

class CaisseSeuilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('seuilAlerte', NumberType::class, [
                'label' => 'Seuil d\'alerte de la caisse',
                'help' => 'Lorsque le solde passera en dessous de cette valeur, une alerte sera envoyée.',
                'required' => true,
                'attr' => [
                    'min' => '0',
                    'max' => '999999.99',
                    'step' => '0.01',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez définir un seuil.']),
                    new PositiveOrZero(['message' => 'Le seuil doit être un nombre positif ou nul.']),
                    new LessThan([
                        'value' => 1000000,
                        'message' => 'Le seuil ne peut pas dépasser {{ compared_value }} €.'
                    ])
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer le seuil',
                'attr' => ['class' => 'btn btn-primary mt-3']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Caisse::class,
        ]);
    }
}
