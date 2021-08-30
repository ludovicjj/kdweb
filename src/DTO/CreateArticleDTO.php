<?php


namespace App\DTO;


use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CreateArticleDTO
{
    /** @var string|null $title */
    private $title;

    /** @var string|null $content */
    private $content;

    /** @var ArrayCollection|null $categories */
    private $categories;

    /** @var UploadedFile|null $picture */
    private $picture;

    public function __construct(
        ?string $title,
        ?string $content,
        $categories = null,
        $picture = null
    )
    {
        $this->title = $title;
        $this->content = $content;
        $this->categories = $categories;
        $this->picture = $picture;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getCategories()
    {
        return $this->categories;
    }

    public function getPicture()
    {
        return $this->picture;
    }
}