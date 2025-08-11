<?php

namespace App\Validator\Constraints;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueEmailValidator extends ConstraintValidator
{
    public function __construct(
        private UserRepository $userRepository
    ) {}

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueEmail) {
            throw new UnexpectedTypeException($constraint, UniqueEmail::class);
        }

        // Si l'objet validé est un User, on récupère son email et son ID
        if ($value instanceof User) {
            $email = $value->getEmail();
            $currentUserId = $value->getId();
        } else {
            return; // Ne pas valider si ce n'est pas un User
        }

        if (null === $email || '' === $email) {
            return; // L'email vide sera géré par NotBlank
        }

        // Chercher un utilisateur existant avec cet email
        $existingUser = $this->userRepository->findOneBy(['email' => $email]);

        // Si un utilisateur existe et que ce n'est pas l'utilisateur actuel (en cas d'édition)
        if ($existingUser && $existingUser->getId() !== $currentUserId) {
            $this->context->buildViolation($constraint->message)
                ->atPath('email')
                ->addViolation();
        }
    }
}
