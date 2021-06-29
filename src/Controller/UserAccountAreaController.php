<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ArticleRepository;
use App\Security\ConfirmPassword;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UserAccountAreaController
 * @package App\Controller
 *
 * @Route("/user/account/profile", name="app_user_account_profile_")
 */
class UserAccountAreaController extends AbstractController
{
    /** @var ArticleRepository $articleRepository */
    private $articleRepository;

    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    /** @var SessionInterface $session */
    private $session;

    /** @var ConfirmPassword $confirmPassword */
    private $confirmPassword;

    public function __construct(
        ArticleRepository $articleRepository,
        EntityManagerInterface $entityManager,
        SessionInterface $session,
        ConfirmPassword $confirmPassword
    ) {
        $this->articleRepository = $articleRepository;
        $this->entityManager = $entityManager;
        $this->session = $session;
        $this->confirmPassword = $confirmPassword;
    }

    /**
     * @Route("/home", name="home", methods={"GET"})
     */
    public function home(): Response
    {
        $this->denyAccessUnlessGranted("ROLE_USER");

        /** @var User $user */
        $user = $this->getUser();
        return $this->render('user_account_area/index.html.twig', [
            'user' => $user,
            'articlesCreatedCount' => $this->articleRepository->getCountArticlesCreatedByUser($user),
            'articlesPublished' => $this->articleRepository->getCountArticlesPublishedByUser($user)
        ]);
    }

    /**
     * @Route("/toggle-checking-ip", name="toggle_checking_ip", methods={"POST"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function toggleGuardCheckingIp(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted("ROLE_USER");
        if (!$request->isXmlHttpRequest()) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'The header "X-Requested-With" is missing.');
        }

        if ($request->headers->get('Toggle-Guard-Checking-IP')) {
            $json = $request->getContent();
            if (!in_array($json, ['true', 'false'], true)) {
                throw new HttpException(Response::HTTP_BAD_REQUEST, 'Expected value is "true" or "false"');
            }
            $this->session->set('Toggle-Guard-Checking-IP', $json);
        }

        $this->confirmPassword->ask();

        $toggleGuardIp = $this->session->get("Toggle-Guard-Checking-IP");

        if ($toggleGuardIp === null) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'The header "Toggle-Guard-Checking-IP" is missing.');
        }

        $this->session->remove("Toggle-Guard-Checking-IP");
        $isGuardCheckIp = filter_var($toggleGuardIp, FILTER_VALIDATE_BOOLEAN);

        /** @var User $user */
        $user = $this->getUser();
        $user->setIsGuardCheckIp($isGuardCheckIp);
        $this->entityManager->flush();

        return $this->json([
            "is_password_confirmed" => true,
            "is_guard_checking_ip" => $isGuardCheckIp
        ]);
    }

    /**
     * @Route("/add-current-ip", name="add_current_ip", methods={"GET", "POST"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function addCurrentUserIpToWhiteList(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted("ROLE_USER");
        if (!$request->isXmlHttpRequest()) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'The header "X-Requested-With" is missing.');
        }

        $this->confirmPassword->ask();

        $userIp = $request->getClientIp();

        /** @var User $user */
        $user = $this->getUser();
        $user->setWhiteListedIpAddresses([$userIp]);
        $this->entityManager->flush();

        return $this->json([
            "is_password_confirmed" => true,
            "user_ip" => $userIp
        ]);
    }

    /**
     * @Route("/edit-user-ip", name="edit_user_ip", methods={"POST"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function editUserIpWhitelist(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted("ROLE_USER");

        if (!$request->isXmlHttpRequest()) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'The header : "X-Requested-With" is missing.');
        }

        if ($request->headers->get("Edit-User-IP")) {
            $json = $request->getContent();
            if (!is_string($json)) {
                throw new HttpException(Response::HTTP_BAD_REQUEST, 'IP addresses entered is invalid.');
            }
            $ipArray = array_filter(explode(',', $json), [$this, "filterIp"]);
            $this->session->set("Edit-User-IP", $ipArray);
        }

        $this->confirmPassword->ask();
        $userIP = $this->session->get("Edit-User-IP");

        if (!$userIP) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'The header : "Edit-User-IP" is missing.');
        }

        /** @var User $user */
        $user = $this->getUser();
        $user->setWhiteListedIpAddresses($userIP);
        $this->entityManager->flush();

        return $this->json([
            "is_password_confirmed" => true,
            "user_ip" => implode(' | ', $userIP)
        ]);
    }

    /**
     * @param string $ip
     * @return mixed
     */
    private function filterIp(string $ip)
    {
        return filter_var($ip, FILTER_VALIDATE_IP);
    }
}
