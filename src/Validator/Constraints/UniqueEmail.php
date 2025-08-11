<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class UniqueEmail extends Constraint
{
    public string $message = 'Cet email est déjà utilisé par un autre utilisateur.';
    
    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
