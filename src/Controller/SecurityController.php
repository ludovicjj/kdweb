<?php

namespace App\Controller;

use App\Repository\AuthLogRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /** @var AuthLogRepository $authLogRepository */
    private $authLogRepository;

    public function __construct(
        AuthLogRepository $authLogRepository
    )
    {
        $this->authLogRepository = $authLogRepository;
    }

    /**
     * @Route("/login", name="app_login", methods={"GET", "POST"}, defaults={"_public_access": true})
     *
     * @param AuthenticationUtils $authenticationUtils
     * @param Request $request
     *
     * @return Response
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function login(AuthenticationUtils $authenticationUtils, Request $request): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $userIP = $request->getClientIp();
        $countRecentLoginFail = 0;

        if ($lastUsername) {
            $countRecentLoginFail = $this->authLogRepository->getRecentFailedAuthAttempt($lastUsername, $userIP);
        }

        return $this->render(
            'security/login.html.twig',
            [
                'last_username' => $lastUsername,
                'error' => $error,
                'count_recent_login_fail' => $countRecentLoginFail
            ]
        );
    }

    /**
     * @Route("/logout", name="app_logout", methods={"GET"}, defaults={"_public_access": true})
     */
    public function logout(): void
    {
        throw new \LogicException('Oops something wrong !');
    }
}
