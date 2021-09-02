<?php


namespace App\Factory;

use App\DTO\CreateArticleDTO;
use App\Entity\Article;
use App\Service\FileUploader;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ArticleFactory
{
    /** @var FileUploader $fileUploader */
    private $fileUploader;

    public function __construct(FileUploader $fileUploader)
    {
        $this->fileUploader = $fileUploader;
    }

    public function build(CreateArticleDTO $dto, bool $isPublished): Article
    {
        /** @var UploadedFile $file */
        $file = $dto->getPicture();

        [
            "fileName" => $fileName,
            "filePath" => $filePath
        ] = $this->fileUploader->upload($file);

        $picture = PictureFactory::build($fileName, $filePath);

        $article = new Article();

        $article
            ->setTitle($dto->getTitle())
            ->setContent($dto->getContent())
            ->setIsPublished($isPublished)
            ->setPicture($picture)
        ;

        foreach ($dto->getCategories() as $category) {
            $article->addCategory($category);
        }

        if ($isPublished) {
            $article->setPublishedAt(new DateTimeImmutable("now"));
        }

        return $article;
    }
}