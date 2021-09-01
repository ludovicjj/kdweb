<?php

namespace App\Handler;

use App\Factory\CreateUserFactory;
use App\Form\RegistrationFormType;
use App\HandlerFactory\AbstractHandler;
use App\Service\SendMail;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use DateTimeImmutable;

class RegistrationHandler extends AbstractHandler
{
    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    /** @var SessionInterface $session */
    private $session;

    /** @var CreateUserFactory $userFactory */
    private $userFactory;

    /** @var SendMail $sendMail */
    private $sendMail;

    public function __construct(
        FormFactoryInterface $formFactory,
        EntityManagerInterface $entityManager,
        SessionInterface $session,
        CreateUserFactory $userFactory,
        SendMail $sendMail
    ) {
        $this->entityManager = $entityManager;
        $this->session = $session;
        $this->userFactory = $userFactory;
        $this->sendMail = $sendMail;
        parent::__construct($formFactory);
    }

    protected function configure(OptionsResolver $resolver): void
    {
        $resolver->setDefault("form_type", RegistrationFormType::class);
    }

    protected function process(): void
    {
        $user = $this->userFactory->build($this->form->getData());

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        /** @var DateTimeImmutable $tokenLifeDateTime */
        $tokenLifeDateTime = $user->getAccountMustBeVerifiedBefore();

        $this->sendMail->send([
            'recipient_email' => $user->getEmail(),
            'subject' => 'Vérification de votre adresse email pour activer votre compte',
            'html_template' => 'emails/registration.html.twig',
            'context' => [
                'userId' => $user->getId(),
                'registrationToken' => $user->getRegistrationToken(),
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