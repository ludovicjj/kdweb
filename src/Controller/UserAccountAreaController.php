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
     * @Route("/toggle-checking-ip", name="toggle_checking_ip", methods={"GET", "POST"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function toggleGuardCheckingIp(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted("ROLE_USER");
        if (!$request->isXmlHttpRequest()) {
            throw new HttpException(
                Response::HTTP_BAD_REQUEST,
                'The header "X-Requested-With" is missing.'
            );
        }
        // Check if header "Toggle-Guard-Checking-Ip"
        if ($request->headers->get('Toggle-Guard-Checking-Ip')) {
            // GET JSON
            $data = $request->getContent();

            // Check JSON value
            if (!in_array($data, ['true', 'false'], true)) {
                throw new HttpException(
                    Response::HTTP_BAD_REQUEST,
                    'Expected value is "true" or "false"'
                );
            }

            // Set new key/value in session
            $this->session->set('Toggle-Guard-Checking-Ip', $data);
        }

        $this->confirmPassword->ask();

        // GET value for key in session
        $toggleGuardIp = $this->session->get("Toggle-Guard-Checking-Ip");

        // Check is value exist
        if ($toggleGuardIp === null) {
            throw new HttpException(
                Response::HTTP_BAD_REQUEST,
                'The header "Toggle-Guard-Checking-Ip" is missing.'
            );
        }

        // Remove Key in session
        $this->session->remove("Toggle-Guard-Checking-Ip");

        $isGuardCheckIp = filter_var($toggleGuardIp, FILTER_VALIDATE_BOOLEAN);

        /** @var User $user */
        $user = $this->getUser();
        $user->setIsGuardCheckIp($isGuardCheckIp);
        $this->entityManager->flush();

        return $this->json([
            "is_guard_checking_ip" => $isGuardCheckIp,
            "is_password_confirmed" => true
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
            "is_password_confirmed" => "IP address added to white list.",
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

        if (!$request->headers->get("Edit-User-IP")) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'The header : "Edit-User-IP" is missing.');
        }

        $data = $request->getContent();

        if (!is_string($data)) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'IP addresses entered is invalid.');
        }

        $ipAddresses = array_filter(explode(',', $data), function ($address) {
            return filter_var($address, FILTER_VALIDATE_IP);
        });

        $this->confirmPassword->ask();

        if (!is_array($ipAddresses)) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, "Expected array with edited user's IP.");
        }

        /** @var User $user */
        $user = $this->getUser();
        $user->setWhiteListedIpAddresses($ipAddresses);
        $this->entityManager->flush();

        return $this->json([
            "is_password_confirmed" => true,
            "user_ip" => implode(' | ', $ipAddresses)
        ]);
    }
}
