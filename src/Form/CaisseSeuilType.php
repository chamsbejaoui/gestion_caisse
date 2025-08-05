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

class CaisseSeuilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('seuilAlerte', NumberType::class, [
                'label' => 'Seuil d\'alerte de la caisse',
                'help' => 'Lorsque le solde passera en dessous de cette valeur, une alerte sera envoyée.',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez définir un seuil.']),
                    new PositiveOrZero(['message' => 'Le seuil doit être un nombre positif ou nul.'])
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
