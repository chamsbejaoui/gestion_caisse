<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use App\Validator\Constraints\UniqueEmail;

class UserForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir une adresse email.',
                    ]),
                    new Email([
                        'message' => 'Veuillez saisir une adresse email valide.',
                    ]),
                ],
            ])
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Utilisateur' => 'ROLE_USER',
                    'Administrateur' => 'ROLE_ADMIN'
                ],
                'multiple' => true,
                'expanded' => true,
                'required' => true,
                'label' => 'Rôles',
                'constraints' => [
                    new Count([
                        'min' => 1,
                        'minMessage' => 'Vous devez sélectionner au moins un rôle.',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Mot de passe',
                'help' => 'Laissez vide pour conserver le mot de passe actuel. Si renseigné : minimum 6 caractères avec au moins 1 minuscule, 1 majuscule et 1 chiffre',
                'attr' => [
                    'minlength' => '6',
                    'maxlength' => '255',
                    'placeholder' => 'Nouveau mot de passe (optionnel)'
                ],
                'constraints' => [
                    new Callback([$this, 'validatePassword']),
                ],
            ])
            ->add('cf_turnstile', HiddenType::class, [
                'mapped' => false,
            ])
        ;
    }

    public function validatePassword($value, ExecutionContextInterface $context): void
    {
        // Si le mot de passe est fourni, il doit respecter les critères
        if (!empty($value)) {
            if (strlen($value) < 6) {
                $context->buildViolation('Votre mot de passe doit contenir au moins 6 caractères')
                    ->addViolation();
            }

            if (strlen($value) > 255) {
                $context->buildViolation('Votre mot de passe ne peut pas dépasser 255 caractères')
                    ->addViolation();
            }

            // Vérification de la force du mot de passe
            if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/', $value)) {
                $context->buildViolation('Le mot de passe doit contenir au moins une minuscule, une majuscule et un chiffre')
                    ->addViolation();
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'constraints' => [
                new UniqueEmail(),
            ],
        ]);
    }
}
