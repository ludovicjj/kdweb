<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UserAccountAreaController
 * @package App\Controller
 *
 * @Route("/user/account/profile", name="app_user_account_")
 */
class UserAccountAreaController extends AbstractController
{
    /** @var ArticleRepository $articleRepository */
    private $articleRepository;

    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    public function __construct(
        ArticleRepository $articleRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->articleRepository = $articleRepository;
        $this->entityManager = $entityManager;
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
     * @Route("/add-ip", name="add_ip", methods={"GET"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function addUserIpToWhiteList(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted("ROLE_USER");
        if (!$request->isXmlHttpRequest()) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'The header "X-Requested-With" is missing.');
        }

        $userIp = $request->getClientIp();

        /** @var User $user */
        $user = $this->getUser();
        $user->setWhiteListedIpAddresses($userIp);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'IP address added to white list.',
            'user_ip' => $userIp
        ]);
    }

    /**
     * @Route("/toggle-checking-ip", name="toggle_check_ip", methods={"POST"})
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

        $switchValue = $request->getContent();

        if (!in_array($switchValue, ['true', 'false'], true)) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Expected value is "true" or "false"');
        }

        /** @var User $user */
        $user = $this->getUser();

        $isSwitchOn = filter_var($switchValue, FILTER_VALIDATE_BOOLEAN);
        $user->setIsGuardCheckIp($isSwitchOn);
        $this->entityManager->flush();

        return $this->json([
            'isGuardCheckingIp' => $isSwitchOn
        ]);
    }
}
