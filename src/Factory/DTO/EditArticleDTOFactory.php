<?php


namespace App\Factory\DTO;

use App\DTO\EditArticleDTO;
use App\Entity\Article;

class EditArticleDTOFactory
{
    public static function build(Article $article): EditArticleDTO
    {
        return new EditArticleDTO(
            $article->getTitle(),
            $article->getContent(),
            $article->getCategories(),
            null
        );

    }
}