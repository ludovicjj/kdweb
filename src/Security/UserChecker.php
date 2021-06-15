<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        // Condition will be executed before guard check if username and password are valid
    }

    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        // User is auth now check if user is verified
        if (!$user->getIsVerified()) {
            throw new CustomUserMessageAccountStatusException(
                "Votre compte n'est pas encore activé. Veuillez vérifié vos e-mail pour activer
                 votre compte avant le 
                {$user->getAccountMustBeVerifiedBefore()->format('d/m/Y à H\hi')}"
            );
        }
    }
}