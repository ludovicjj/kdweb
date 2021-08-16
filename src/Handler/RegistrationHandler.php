<?php

namespace App\Handler;

use App\DTO\RegistrationDTO;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\HandlerFactory\AbstractHandler;
use App\Service\SendMail;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use DateTimeImmutable;
use DateInterval;

class RegistrationHandler extends AbstractHandler
{
    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    /** @var SessionInterface $session */
    private $session;

    /** @var TokenGeneratorInterface $tokenGenerator */
    private $tokenGenerator;

    /** @var SendMail $sendMail */
    private $sendMail;

    public function __construct(
        FormFactoryInterface $formFactory,
        EntityManagerInterface $entityManager,
        SessionInterface $session,
        TokenGeneratorInterface $tokenGenerator,
        SendMail $sendMail
    ) {
        $this->entityManager = $entityManager;
        $this->session = $session;
        $this->tokenGenerator = $tokenGenerator;
        $this->sendMail = $sendMail;
        parent::__construct($formFactory);
    }

    protected function configure(OptionsResolver $resolver): void
    {
        $resolver->setDefault("form_type", RegistrationFormType::class);
    }

    protected function process(): void
    {
        /** @var RegistrationDTO $dto */
        $dto = $this->form->getData();

        /** @var string $email */
        $email = $dto->getEmail();

        /** @var string $plainPassword */
        $plainPassword = $dto->getPassword();

        $registrationToken = $this->tokenGenerator->generateToken();
        $user = new User();
        $user
            ->setEmail($email)
            ->setRegistrationToken($registrationToken)
            ->setPassword($plainPassword)
            ->setAccountMustBeVerifiedBefore((new DateTimeImmutable('now'))->add(new DateInterval("P1D")))
            ->setIsVerified(false);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        /** @var DateTimeImmutable $tokenLifeDateTime */
        $tokenLifeDateTime =  $user->getAccountMustBeVerifiedBefore();

        $this->sendMail->send([
            'recipient_email' => $user->getEmail(),
            'subject' => 'Vérification de votre adresse email pour activer votre compte',
            'html_template' => 'emails/registration.html.twig',
            'context' => [
                'userId' => $user->getId(),
                'registrationToken' => $registrationToken,
                'tokenLifeTime' => $tokenLifeDateTime->format('d/m/Y à H:i')
            ]
        ]);

        /** @var FlashBagInterface $flashBag */
        $flashBag = $this->session->getFlashBag();
        $flashBag->add(
            "success",
            "Votre inscription a été effectué avec success. Veuillez consulter vos e-mails pour activer votre compte"
        );
    }
}