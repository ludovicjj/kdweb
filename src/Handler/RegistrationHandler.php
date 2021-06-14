<?php

namespace App\Handler;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\HandlerFactory\AbstractHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class RegistrationHandler extends AbstractHandler
{
    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    /** @var FlashBagInterface $flashBag */
    private $flashBag;

    /** @var TokenGeneratorInterface $tokenGenerator */
    private $tokenGenerator;

    /** @var UserPasswordEncoderInterface $passwordEncoder */
    private $passwordEncoder;

    public function __construct(
        FormFactoryInterface $formFactory,
        EntityManagerInterface $entityManager,
        FlashBagInterface $flashBag,
        TokenGeneratorInterface $tokenGenerator,
        UserPasswordEncoderInterface $passwordEncoder
    ) {
        $this->entityManager = $entityManager;
        $this->flashBag = $flashBag;
        $this->tokenGenerator = $tokenGenerator;
        $this->passwordEncoder = $passwordEncoder;
        parent::__construct($formFactory);
    }

    protected function configure(OptionsResolver $resolver): void
    {
        $resolver->setDefault("form_type", RegistrationFormType::class);
    }

    protected function process(): void
    {
        $user = new User();
        $user->setRegistrationToken($this->tokenGenerator->generateToken())
             ->setPassword($this->passwordEncoder->encodePassword($user, $this->form->get('password')->getData()));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->flashBag->add("success", "Votre inscription a été effectué avec success.");
    }
}