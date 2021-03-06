<?php

namespace App\DTO;

use App\Entity\Category;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class CreateArticleDTO
{
    /**
     * @var string|null $title
     * @Assert\NotBlank(
     *     message="Le champs titre est obligatoire."
     * )
     * @Assert\Length(
     *     min = 3,
     *     max = 255,
     *     minMessage = "Le titre doit au moins contenir {{ limit }} caracters",
     *     maxMessage = "Le titre ne peux dépasser {{ limit }} caracters"
     * )
     */
    private $title;

    /**
     * @var string|null $content
     *
     * @Assert\NotBlank(
     *     message="Le champs contenu est obligatoire."
     * )
     */
    private $content;

    /**
     * @Assert\NotNull(
     *     message="Vous devez choisir une catégorie."
     * )
     * @var ArrayCollection|null $categories
     */
    private $categories;

    /**
     * @var UploadedFile|null $picture
     *
     * @Assert\Image(
     *     maxSize = "1M",
     *     maxSizeMessage = "Maximum {{ limit }} {{ suffix }}"
     * )
     */
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