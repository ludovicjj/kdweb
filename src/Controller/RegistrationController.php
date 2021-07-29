<?php

namespace App\Controller;

use App\Handler\RegistrationHandler;
use App\HandlerFactory\HandlerFactory;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use DateTimeImmutable;

class RegistrationController extends AbstractController
{
    /** @var HandlerFactory $handlerFactory */
    private $handlerFactory;

    /** @var EntityManagerInterface $entityManger */
    private $entityManger;

    /** @var UserRepository $userRepository */
    private $userRepository;

    public function __construct(
        HandlerFactory $handlerFactory,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository
    ) {
        $this->handlerFactory = $handlerFactory;
        $this->entityManger = $entityManager;
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/register", name="app_register", methods={"GET", "POST"})
     *
     * @param Request $request
     * @return Response
     */
    public function register(Request $request): Response
    {
        $handler = $this->handlerFactory->createHandler(RegistrationHandler::class);

        if ($handler->handle($request)) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $handler->createView(),
        ]);
    }

    /**
     * @Route("/{id<\d+>}/{token}", name="app_verify_account", methods={"GET"})
     *
     * @param Request $request
     * @return Response
     */
    public function verifyUserAccount(Request $request): Response
    {
        $user = $this->userRepository->findOneBy(['id' => $request->attributes->get('id')]);

        if ($user === null) {
            throw new AccessDeniedException();
        }

        if (
            $user->getRegistrationToken() === null ||
            $user->getRegistrationToken() !== $request->attributes->get('token') ||
            $user->getAccountMustBeVerifiedBefore() === null ||
            $this->isVerifiedBeforeEndTime($user->getAccountMustBeVerifiedBefore())
        ) {
            throw new AccessDeniedException();
        }

        $user
            ->setIsVerified(true)
            ->setAccountVerifiedAt(new DateTimeImmutable('now'))
            ->setRegistrationToken(null)
        ;

        $this->entityManger->flush();
        $this->addFlash("success", "Votre compte est dés à présent activé. Vous pouvez vous authentifié");

        return $this->redirect('app_login');
    }

    private function isVerifiedBeforeEndTime(DateTimeImmutable $endDate): bool
    {
        $now = new DateTimeImmutable('now');
        return $now > $endDate;
    }
}
