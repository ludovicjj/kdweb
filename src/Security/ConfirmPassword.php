<?php

namespace App\Security;

use App\Entity\User;
use App\Event\ConfirmPasswordEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;
use LogicException;

class ConfirmPassword
{
    /** @var EventDispatcherInterface $eventDispatcher */
    private $eventDispatcher;

    /** @var Security $security */
    private $security;

    /** @var RequestStack $requestStack */
    private $requestStack;

    /** @var Session $session */
    private $session;

    /** @var UserPasswordEncoderInterface $passwordEncoder */
    private $passwordEncoder;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        Security $security,
        RequestStack $requestStack,
        Session $session,
        UserPasswordEncoderInterface $passwordEncoder
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->security = $security;
        $this->requestStack = $requestStack;
        $this->session = $session;
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * Display the password confirmation modal in case to sensitive operation
     */
    public function ask(): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            throw new LogicException("Oops something wrong happen !");
        }

        if (!$request->headers->get('Confirm-Identity-With-Password')) {
            $this->dispatchDisplayModalEvent();
        }

        $this->dispatchInvalidPasswordEventOrContinue($request);
    }

    /**
     * Dispatch a modal display event.
     * To display a modal window.
     */
    private function dispatchDisplayModalEvent(): void
    {
        $this->eventDispatcher->dispatch(new ConfirmPasswordEvents(), ConfirmPasswordEvents::MODAL_DISPLAY);
    }

    /**
     * Dispatch a password invalid event if the user entered an invalid confirmation password
     * If the confirmation password is valid, then let the request continue.
     *
     * @param Request $request
     */
    private function dispatchInvalidPasswordEventOrContinue(Request $request): void
    {
        /** @var string $json */
        $json = $request->getContent();
        $data = json_decode($json, true);

        // check bad format json

        if (!array_key_exists("password", $data)) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, "Missing password");
        }

        $enteredPassword = $data["password"];

        /** @var User $user */
        $user = $this->security->getUser();

        if (!$this->passwordEncoder->isPasswordValid($user, $enteredPassword)) {
            $this->invalidateSession();
            $this->eventDispatcher->dispatch(new ConfirmPasswordEvents(), ConfirmPasswordEvents::PASSWORD_INVALID);
        }

        if ($this->session->has("Password-Confirmation-Invalid")) {
            $this->session->remove("Password-Confirmation-Invalid");
        }
    }

    /**
     * Invalid user's session if confirmation password is invalide 3 times
     */
    private function invalidateSession()
    {
        if (!$this->session->get("Password-Confirmation-Invalid")) {
            $this->session->set("Password-Confirmation-Invalid", 1);
        } else {
            $this->session->set(
                "Password-Confirmation-Invalid",
                $this->session->get("Password-Confirmation-Invalid") + 1
            );

            if ($this->session->get("Password-Confirmation-Invalid") === 3) {
                $this->session->invalidate();
                $this->session->getFlashBag()->add(
                    "danger",
                    "Vous avez été déconnecté par mesure de sécurité car 3 mots de passe invalides 
                    ont été saisis lors de la confirmation du mot de passe."
                );

                $this->eventDispatcher->dispatch(new ConfirmPasswordEvents(), ConfirmPasswordEvents::PASSWORD_INVALID);
            }
        }
    }
}