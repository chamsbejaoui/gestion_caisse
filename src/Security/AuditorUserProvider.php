<?php

namespace App\Security;

use DH\Auditor\Security\SecurityProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AuditorUserProvider implements SecurityProviderInterface
{
    private TokenStorageInterface $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function __invoke(): array
    {
        $token = $this->tokenStorage->getToken();

        if (!$token instanceof TokenInterface) {
            return [];
        }

        $user = $token->getUser();

        if (!is_object($user)) {
            return [];
        }

        return [
            'id' => $user instanceof \App\Entity\User ? $user->getId() : null,

            'username' => method_exists($user, 'getUserIdentifier') ? $user->getUserIdentifier() : 'anon.',
            'roles' => method_exists($user, 'getRoles') ? $user->getRoles() : [],
        ];
    }
}
