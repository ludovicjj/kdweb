<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    /** @var RequestStack $requestStack */
    private $requestStack;

    public function __construct(
        RequestStack $requestStack
    ) {
        $this->requestStack = $requestStack;
    }

    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        // Condition will be executed before guard check if password is valid
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

        if ($user->getIsGuardCheckIp() && !$this->isWhitelistedUserIp($user)) {
            throw new CustomUserMessageAccountStatusException(
                "Vous n'êtes pas autorisé à vous authentifier avec cette adresse IP."
            );
        }
    }

    /**
     * Check if whitelist contain current user's ip
     *
     * @param User $user
     * @return bool
     */
    private function isWhitelistedUserIp(User $user): bool
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request) {
            return false;
        }

        $userIp = $request->getClientIp();
        $whitelistedUserIp = $user->getWhiteListedIpAddresses();

        return in_array($userIp, $whitelistedUserIp, true);
    }
}