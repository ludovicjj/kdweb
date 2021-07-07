<?php

namespace App\Controller;

use App\Entity\User;
use App\Handler\ForgotPasswordHandler;
use App\Handler\ResetPasswordHandler;
use App\HandlerFactory\HandlerFactory;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use DateTimeImmutable;

class ForgotPasswordController extends AbstractController
{
    /** @var HandlerFactory $handlerFactory */
    private $handlerFactory;

    /** @var SessionInterface $session */
    private $session;

    /** @var UserRepository $userRepository */
    private $userRepository;

    public function __construct(
        HandlerFactory $handlerFactory,
        SessionInterface $session,
        UserRepository $userRepository
    )
    {
        $this->handlerFactory = $handlerFactory;
        $this->session = $session;
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/forgot/password", name="app_forgot_password", methods={"GET", "POST"})
     *
     * @param Request $request
     * @return Response
     */
    public function forgotPassword(Request $request): Response
    {
        $handler = $this->handlerFactory->createHandler(ForgotPasswordHandler::class);

        if ($handler->handle($request)) {
            return $this->redirectToRoute("app_login");
        }

        return $this->render("password/forgot_password.html.twig", [
                "forgotPasswordForm" => $handler->createView()
            ]
        );
    }

    /**
     * @Route("/reset/password/{id<\d+>}/{token}", name="app_retrieve_credential", methods={"GET"})
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function retrieveCredentialsFromUrl(Request $request): RedirectResponse
    {
        $this->session->set("Reset-Password-User-ID", $request->attributes->get('id'));
        $this->session->set("Reset-Password-Token", $request->attributes->get('token'));

        return $this->redirectToRoute("app_reset_password");
    }

    /**
     * @Route("/reset/password", name="app_reset_password", methods={"GET", "POST"})
     * @param Request $request
     * @return Response
     */
    public function resetPassword(Request $request): Response
    {
        $userID = $this->session->get("Reset-Password-User-ID", null);

        $user = $this->userRepository->findOneBy(["id" => $userID]);

        if (!$user) {
            return $this->redirectToRoute("app_forgot_password");
        }

        if (!$this->isValidResetPasswordToken($user)) {
            return $this->redirectToRoute("app_forgot_password");
        }

        $handler = $this->handlerFactory->createHandler(ResetPasswordHandler::class);

        if ($handler->handle($request, null, $user)) {
            return $this->redirectToRoute("app_login");
        }

        return $this->render("password/reset_password.html.twig", [
                "resetPasswordForm" => $handler->createView(),
                "passwordMustBeResetBefore" => $this->getEndlifeResetToken($user)
            ]
        );
    }

    /**
     * Check if reset password token is valid
     *
     * @param User $user
     * @return bool
     */
    private function isValidResetPasswordToken(User $user): bool
    {
        /** @var DateTimeImmutable $tokenVerifiedBefore */
        $tokenVerifiedBefore = $user->getForgotPasswordTokenMustBeVerifiedBefore();
        $token = $this->session->get("Reset-Password-Token", null);

        if (
            $user->getForgotPasswordToken() === null ||
            $user->getForgotPasswordToken() !== $token ||
            new DateTimeImmutable('now') > $tokenVerifiedBefore
        )
        {
            return false;
        }

        return true;
    }

    /**
     * Return the time before which password be be modified.
     * Exemple: 15h04
     *
     * @param User $user
     * @return string
     */
    private function getEndLifeResetToken(User $user): string
    {
        /** @var DateTimeImmutable $end */
        $end = $user->getForgotPasswordTokenMustBeVerifiedBefore();
        return $end->format("H\hi");
    }
}