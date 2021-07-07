<?php

namespace App\Handler;

use App\DTO\ForgotPasswordDTO;
use App\Form\ForgotPasswordType;
use App\HandlerFactory\AbstractHandler;
use App\Repository\UserRepository;
use App\Service\SendMail;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use DateTimeImmutable;

class ForgotPasswordHandler extends AbstractHandler
{
    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    /** @var UserRepository $userRepository */
    private $userRepository;

    /** @var SendMail $sendMail */
    private $sendMail;

    /** @var TokenGeneratorInterface $tokenGenerator */
    private $tokenGenerator;

    /** @var SessionInterface $session */
    private $session;

    public function __construct(
        FormFactoryInterface $formFactory,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        SendMail $sendMail,
        TokenGeneratorInterface $tokenGenerator,
        SessionInterface $session
    )
    {
        parent::__construct($formFactory);
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->sendMail = $sendMail;
        $this->tokenGenerator = $tokenGenerator;
        $this->session = $session;
    }

    protected function configure(OptionsResolver $resolver): void
    {
        $resolver->setDefault("form_type", ForgotPasswordType::class);
    }

    protected function process(): void
    {
        /** @var ForgotPasswordDTO $dto */
        $dto = $this->form->getData();

        $user = $this->userRepository->findOneBy(['email' => $dto->getEmail()]);

        // Always send flash message although account doesn't exist.
        // Hide if one account exist or not.
        if (!$user) {
            $this->session->getFlashBag()->add(
                "success",
                "Un e-mail vous a été envoyé pour réinitialiser votre mot de passe"
            );
            return;
        }

        $user->setForgotPasswordToken($this->tokenGenerator->generateToken())
            ->setForgotPasswordTokenRequestedAt(new DateTimeImmutable('now'))
            ->setForgotPasswordTokenMustBeVerifiedBefore(new DateTimeImmutable('+15 minutes'));

        $this->entityManager->flush();
        $this->sendMail->send([
            'recipient_email' => $user->getUsername(),
            'subject' => "Modification de votre mot de passe",
            'html_template' => 'emails/forgot_password.html.twig',
            'context' => [
                'user' => $user,
                'tokenLifeTime' => $user->getForgotPasswordTokenMustBeVerifiedBefore()->format('d/m/Y à H:i')
            ]
        ]);

        $this->session->getFlashBag()->add(
            "success",
            "Un e-mail vous a été envoyé pour réinitialiser votre mot de passe"
        );
    }
}