<?php

namespace App\Entity;

use App\Repository\PictureRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @ORM\Entity(repositoryClass=PictureRepository::class)
 */
class Picture
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @var int $id
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string $picturePath
     */
    private $picturePath;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string $pictureName
     */
    private $pictureName;

    /**
     * @ORM\OneToOne(targetEntity=Article::class, inversedBy="picture", cascade={"persist", "remove"})
     * @var Article|null $article
     */
    private $article;

    /**
     * @var UploadedFile|null $image
     */
    private $image;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPicturePath(): ?string
    {
        return $this->picturePath;
    }

    public function setPicturePath(string $picturePath): self
    {
        $this->picturePath = $picturePath;

        return $this;
    }

    public function getPictureName(): ?string
    {
        return $this->pictureName;
    }

    public function setPictureName(string $pictureName): self
    {
        $this->pictureName = $pictureName;

        return $this;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): self
    {
        $this->article = $article;

        return $this;
    }

    public function setImage(UploadedFile $image): self
    {
        $this->image = $image;
        return $this;
    }

    public function getImage(): ?UploadedFile
    {
        return $this->image;
    }
}
