<?php

namespace App\Validator\Constraints;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueEmailValidator extends ConstraintValidator
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function validate($user, Constraint $constraint)
    {
        if (!$constraint instanceof UniqueEmail) {
            throw new UnexpectedTypeException($constraint, UniqueEmail::class);
        }

        if (!$user instanceof User) {
            return;
        }

        $email = $user->getEmail();
        if (!$email) {
            return;
        }

        // Vérifier si l'email existe déjà pour un autre utilisateur
        $existingUser = $this->userRepository->findOneBy(['email' => $email]);
        
        // Si un utilisateur avec cet email existe et ce n'est pas l'utilisateur actuel (cas d'édition)
        if ($existingUser && $existingUser->getId() !== $user->getId()) {
            $this->context->buildViolation($constraint->message)
                ->atPath('email')
                ->addViolation();
        }
    }
}