<?php

namespace App\Handler;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\HandlerFactory\AbstractHandler;
use App\Service\SendMail;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class RegistrationHandler extends AbstractHandler
{
    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    /** @var SessionInterface $session */
    private $session;

    /** @var TokenGeneratorInterface $tokenGenerator */
    private $tokenGenerator;

    /** @var UserPasswordEncoderInterface $passwordEncoder */
    private $passwordEncoder;

    /** @var SendMail $sendMail */
    private $sendMail;

    public function __construct(
        FormFactoryInterface $formFactory,
        EntityManagerInterface $entityManager,
        SessionInterface $session,
        TokenGeneratorInterface $tokenGenerator,
        UserPasswordEncoderInterface $passwordEncoder,
        SendMail $sendMail
    ) {
        $this->entityManager = $entityManager;
        $this->session = $session;
        $this->tokenGenerator = $tokenGenerator;
        $this->passwordEncoder = $passwordEncoder;
        $this->sendMail = $sendMail;
        parent::__construct($formFactory);
    }

    protected function configure(OptionsResolver $resolver): void
    {
        $resolver->setDefault("form_type", RegistrationFormType::class);
    }

    protected function process(): void
    {
        $registrationToken = $this->tokenGenerator->generateToken();
        $user = new User();
        $user
            ->setEmail($this->form->get('email')->getData())
            ->setRegistrationToken($registrationToken)
            ->setPassword($this->passwordEncoder->encodePassword($user, $this->form->get('password')->getData()));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->sendMail->send([
            'recipient_email' => $user->getEmail(),
            'subject' => 'Vérification de votre adresse email pour activer votre compte',
            'html_template' => 'emails/registration.html.twig',
            'context' => [
                'userId' => $user->getId(),
                'registrationToken' => $registrationToken,
                'tokenLifeTime' => $user->getAccountMustBeVerifiedBefore()->format('d/m/Y à H:i')
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