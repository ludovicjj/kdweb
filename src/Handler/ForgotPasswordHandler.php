<?php

namespace App\Handler;

use App\Form\ForgotPasswordType;
use App\HandlerFactory\AbstractHandler;
use App\Repository\UserRepository;
use App\Service\SendMail;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

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

    public function __construct(
        FormFactoryInterface $formFactory,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        SendMail $sendMail,
        TokenGeneratorInterface $tokenGenerator
    )
    {
        parent::__construct($formFactory);
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->sendMail = $sendMail;
        $this->tokenGenerator = $tokenGenerator;
    }

    protected function configure(OptionsResolver $resolver): void
    {
        $resolver->setDefault("form_type", ForgotPasswordType::class);
    }

    protected function process(): void
    {
        // TODO: Implement process() method.
    }
}