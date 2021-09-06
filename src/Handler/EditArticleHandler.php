<?php

namespace App\Handler;

use App\DTO\EditArticleDTO;
use App\Entity\Article;
use App\Entity\Picture;
use App\Form\EditArticleType;
use App\HandlerFactory\AbstractHandler;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use DateTimeImmutable;

class EditArticleHandler extends AbstractHandler
{
    /** @var FileUploader $fileUploader */
    private $fileUploader;

    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    /** @var Filesystem $fileSystem */
    private $fileSystem;

    public function __construct(
        FormFactoryInterface $formFactory,
        FileUploader $fileUploader,
        EntityManagerInterface $entityManager,
        Filesystem $fileSystem
    )
    {
        $this->fileUploader = $fileUploader;
        $this->entityManager = $entityManager;
        $this->fileSystem = $fileSystem;
        parent::__construct($formFactory);
    }

    protected function configure(OptionsResolver $resolver): void
    {
        $resolver->setDefault("form_type", EditArticleType::class);
    }

    protected function process(): void
    {
        /** @var EditArticleDTO $dto */
        $dto = $this->form->getData();

        /** @var Article $article */
        $article = $this->entity;

        $article
            ->setTitle($dto->getTitle())
            ->setContent($dto->getContent());

        foreach ($dto->getCategories() as $category) {
            $article->addCategory($category);
        }

        //todo form contain new article's picture.
        if ($dto->getPicture() !== null) {
            /** @var Picture $picture */
            $picture = $article->getPicture();

            //todo remove old picture file.
            if ($this->fileSystem->exists($picture->getPicturePath())) {
                $this->fileSystem->remove($picture->getPicturePath());
            }

            //todo update picture data
            $pictureInfo = $this->fileUploader->upload($dto->getPicture());
            $picture->setPictureName($pictureInfo["fileName"])
                    ->setPicturePath($pictureInfo["filePath"]);
        }

        //todo define article as published
        if (!$article->getIsPublished()) {
            $article
                ->setIsPublished(true)
                ->setPublishedAt(new DateTimeImmutable("now"));
        }

        $this->entityManager->flush(); // listener define properties updatedAt and slug with new title
    }
}