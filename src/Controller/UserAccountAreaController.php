<?php

namespace App\Controller;

use App\DTO\ResetPasswordDTO;
use App\Entity\User;
use App\Form\ResetPasswordType;
use App\Repository\ArticleRepository;
use App\Security\ConfirmPassword;
use App\Utils\LogoutUserTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class UserAccountAreaController
 * @package App\Controller
 *
 * @Route("/user/account", name="app_user_account_")
 */
class UserAccountAreaController extends AbstractController
{
    use LogoutUserTrait;

    /** @var ArticleRepository $articleRepository */
    private $articleRepository;

    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    /** @var SessionInterface $session */
    private $session;

    /** @var ConfirmPassword $confirmPassword */
    private $confirmPassword;

    /** @var TokenStorageInterface $tokenStorage */
    private $tokenStorage;

    /** @var ValidatorInterface $validator */
    private $validator;

    public function __construct(
        ArticleRepository $articleRepository,
        EntityManagerInterface $entityManager,
        SessionInterface $session,
        ConfirmPassword $confirmPassword,
        TokenStorageInterface $tokenStorage,
        ValidatorInterface $validator
    ) {
        $this->articleRepository = $articleRepository;
        $this->entityManager = $entityManager;
        $this->session = $session;
        $this->confirmPassword = $confirmPassword;
        $this->tokenStorage = $tokenStorage;
        $this->validator = $validator;
    }

    /**
     * @Route("/home", name="home", methods={"GET"})
     */
    public function home(): Response
    {
        $this->denyAccessUnlessGranted("ROLE_USER");
        $form = $this->createForm(ResetPasswordType::class, null, [
            "action" => $this->generateUrl("app_user_account_modify_password"),
            "attr" => [
                "class" => "mt-3"
            ]
        ]);

        /** @var User $user */
        $user = $this->getUser();
        return $this->render('user_account_area/index.html.twig', [
            'user' => $user,
            'articlesCreatedCount' => $this->articleRepository->getCountArticlesCreatedByUser($user),
            'articlesPublished' => $this->articleRepository->getCountArticlesPublishedByUser($user),
            'modifyPasswordForm' => $form->createView()
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
     * @Route("/add-user-ip", name="add_user_ip", methods={"GET", "POST"})
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

        $this->confirmPassword->ask();

        $userIp = $request->getClientIp();

        /** @var User $user */
        $user = $this->getUser();
        $user->setWhiteListedIpAddresses(array_unique(array_merge($user->getWhiteListedIpAddresses(), [$userIp])));
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
        $this->session->remove("Edit-User-IP");

        if ($userIP === null) {
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
     * @Route("/modify-password", name="modify_password", methods={"POST"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function modifyPassword(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted("ROLE_USER");

        if (!$request->isXmlHttpRequest()) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'The header : "X-Requested-With" is missing.');
        }

        if ($request->headers->get("Password-Modification")) {
            $json = $request->getContent();

            if (!is_string($json)) {
                throw new HttpException(Response::HTTP_BAD_REQUEST, 'Password entered invalid. Expected string');
            }
            $data = json_decode($json, true);

            if ($data === null) {
                throw new HttpException(Response::HTTP_BAD_REQUEST, 'Invalid json.');
            }

            if (!array_key_exists('password', $data)) {
                throw new HttpException(Response::HTTP_BAD_REQUEST, 'Expected password in request body.');
            }

            $passwordEntered = $data['password'];

            $constraintViolationList = $this->validator->validatePropertyValue(
                ResetPasswordDTO::class,
                "password",
                $passwordEntered
            );

            if (count($constraintViolationList) > 0) {
                throw new HttpException(Response::HTTP_BAD_REQUEST, $constraintViolationList[0]->getMessage());
            }

            $this->session->set('Password-Modification-Entered', $passwordEntered);
        }

        $this->confirmPassword->ask();
        /** @var User $user */
        $user = $this->getUser();
        $plainPassword = $this->session->get('Password-Modification-Entered');

        if ($plainPassword === null) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'The header : "Password-Modification" is missing.');
        }

        $user->setPassword($plainPassword);
        $this->entityManager->flush();

        return $this->logoutUser(
            $request,
            $this->session,
            $this->tokenStorage,
            "success",
            "Votre mot de passe a été modifié. Vous pouvez à present vous connecter.",
            true,
            true
        );

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
