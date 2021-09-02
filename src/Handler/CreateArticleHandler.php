<?php

namespace App\Handler;

use App\Factory\ArticleFactory;
use App\Form\CreateArticleType;
use App\HandlerFactory\AbstractHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use LogicException;

class CreateArticleHandler extends AbstractHandler
{
    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    /** @var ArticleFactory $articleFactory */
    private $articleFactory;

    public function __construct(
        FormFactoryInterface $formFactory,
        EntityManagerInterface $entityManager,
        ArticleFactory $articleFactory
    )
    {
        $this->entityManager = $entityManager;
        $this->articleFactory = $articleFactory;
        parent::__construct($formFactory);
    }

    protected function configure(OptionsResolver $resolver): void
    {
        $resolver->setDefault("form_type", CreateArticleType::class);
    }

    protected function process(): void
    {
        /** @var Form $form */
        $form = $this->form;

        $button = $form->getClickedButton();

        if ($button === null) {
            throw new LogicException("Missing submit button in form");
        }

        $isPublished = $button->getName() === "publish";
        $article = $this->articleFactory->build($this->form->getData(), $isPublished);
        $this->entityManager->persist($article); // Listener define author, slug and createdAt
        $this->entityManager->flush();
    }
}