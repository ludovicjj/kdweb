<?php


namespace App\Handler;


use App\Form\ForgotPasswordType;
use App\HandlerFactory\AbstractHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ForgotPasswordHandler extends AbstractHandler
{
    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    public function __construct(
        FormFactoryInterface $formFactory,
        EntityManagerInterface $entityManager
    )
    {
        parent::__construct($formFactory);
        $this->entityManager = $entityManager;
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