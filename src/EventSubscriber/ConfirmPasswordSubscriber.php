<?php


namespace App\EventSubscriber;

use App\Event\ConfirmPasswordEvents;
use App\Utils\LogoutUserTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ConfirmPasswordSubscriber implements EventSubscriberInterface
{
    use LogoutUserTrait;

    /** @var RequestStack $requestStack */
    private $requestStack;

    /** @var SessionInterface $session */
    private $session;

    /** @var TokenStorageInterface $tokenStorage */
    private $tokenStorage;

    public function __construct(
        RequestStack $requestStack,
        SessionInterface $session,
        TokenStorageInterface $tokenStorage
    )
    {
        $this->requestStack = $requestStack;
        $this->session = $session;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @return array<string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ConfirmPasswordEvents::MODAL_DISPLAY => "onModalDisplay",
            ConfirmPasswordEvents::PASSWORD_INVALID => "onPasswordInvalid",
            ConfirmPasswordEvents::SESSION_INVALIDATE => "onSessionInvalidate",
        ];
    }

    public function onModalDisplay(ConfirmPasswordEvents $event): void
    {
        $this->sendJsonResponse();
    }

    public function onPasswordInvalid(ConfirmPasswordEvents $event): void
    {
        $this->sendJsonResponse();
    }

    public function onSessionInvalidate(ConfirmPasswordEvents $event): void
    {
        $this->sendJsonResponse(true);
    }

    /**
     * Send JSON response and exit the parent request.
     *
     * @param bool $isUserDeauthenticated
     */
    private function sendJsonResponse(bool $isUserDeauthenticated = false): void
    {
        if ($isUserDeauthenticated) {
            $request = $this->requestStack->getCurrentRequest();
            if (!$request) {
                return;
            }

            $response = $this->logoutUser(
                $request,
                $this->session,
                $this->tokenStorage,
                "danger",
                "Vous avez été déconnecté par mesure de sécurité car 3 mot de passe invalides ont été saisi lors de la confirmation du mot de passe.",
                false,
                true
            );
            $response->send();
            exit();
        }
        $response = new JsonResponse([
            "is_password_confirmed" => false,
        ]);
        $response->send();
        exit();

    }
}