<?php

namespace App\Handler;

use App\DTO\ResetPasswordDTO;
use App\Entity\User;
use App\Form\ResetPasswordType;
use App\HandlerFactory\AbstractHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use DateTimeImmutable;

class ResetPasswordHandler extends AbstractHandler
{
    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    /** @var SessionInterface $session */
    private $session;

    public function __construct(
        FormFactoryInterface $formFactory,
        EntityManagerInterface $entityManager,
        SessionInterface $session
    )
    {
        parent::__construct($formFactory);
        $this->entityManager = $entityManager;
        $this->session = $session;
    }

    protected function configure(OptionsResolver $resolver): void
    {
        $resolver->setDefault("form_type", ResetPasswordType::class);
    }

    protected function process(): void
    {
        /** @var ResetPasswordDTO $dto */
        $dto = $this->form->getData();

        if ($this->entity === null || $dto->getPassword() === null) {
            return;
        }

        /** @var User $user */
        $user = $this->entity;
        $user->setForgotPasswordToken(null)
            ->setForgotPasswordTokenRequestedAt(new DateTimeImmutable('now'))
            ->setPassword($dto->getPassword());
        $this->entityManager->flush();

        // clear session
        $this->session->remove("Reset-Password-User-ID");
        $this->session->remove("Reset-Password-Token");

        $this->session->getFlashBag()->add('success', "Votre mot de passe a été réinitialiser, vous pouvez vous connecter.");
    }
}