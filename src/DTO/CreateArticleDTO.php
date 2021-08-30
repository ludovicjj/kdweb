<?php


namespace App\DTO;


use App\Entity\Category;
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

    public function addCategory(Category $category): void
    {
        if ($this->categories->contains($category)) {
            return;
        }
        $this->categories[] = $category;
    }

    public function removeCategory(Category $category): void
    {
        $this->categories->removeElement($category);
    }

    public function getPicture()
    {
        return $this->picture;
    }
}